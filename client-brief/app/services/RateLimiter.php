<?php
declare(strict_types=1);

final class RateLimiter
{
    /**
     * Read-only check whether a bucket is currently blocked.
     * Fails closed if both database and fallback file storage fail.
     */
    public static function isBlocked(string $bucketKey, int $maxAttempts, int $windowSeconds): bool
    {
        $now = time();
        try {
            $pdo = Connection::getPdo();
            $stmt = $pdo->prepare('SELECT attempts, first_attempt_at, locked_until FROM rate_limits WHERE bucket_key = :key LIMIT 1');
            $stmt->execute([':key' => $bucketKey]);
            $row = $stmt->fetch();

            if (!$row) {
                return false;
            }

            if ($row['locked_until'] !== null && (int) $row['locked_until'] > $now) {
                return true;
            }

            if (($now - (int) $row['first_attempt_at']) > $windowSeconds) {
                return false;
            }

            return (int) $row['attempts'] >= $maxAttempts;
        } catch (Throwable $e) {
            SecurityService::logError('RateLimiter::isBlocked DB fallback: ' . $e->getMessage());
            return self::isBlockedFile($bucketKey, $maxAttempts, $windowSeconds, $now);
        }
    }

    /**
     * Atomically verify limit and consume one attempt for a bucket.
     * Returns true if allowed, false if blocked or if storage fails closed.
     */
    public static function attempt(string $bucketKey, int $maxAttempts, int $windowSeconds): bool
    {
        $now = time();
        try {
            $pdo = Connection::getPdo();
            $ownTx = !$pdo->inTransaction();
            if ($ownTx) {
                $pdo->beginTransaction();
            }

            // Ensure bucket row exists and acquire X-lock in one atomic step (avoids S->X deadlock)
            $ensure = $pdo->prepare(
                'INSERT INTO rate_limits (bucket_key, attempts, first_attempt_at, locked_until)
                 VALUES (:key, 0, :now_ins, NULL)
                 ON DUPLICATE KEY UPDATE attempts = attempts'
            );
            $ensure->execute([
                ':key' => $bucketKey,
                ':now_ins' => $now,
            ]);

            $select = $pdo->prepare(
                'SELECT id, attempts, first_attempt_at, locked_until
                 FROM rate_limits
                 WHERE bucket_key = :key
                 FOR UPDATE'
            );
            $select->execute([':key' => $bucketKey]);
            $row = $select->fetch();

            if (!$row) {
                if ($ownTx && $pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                return false;
            }

            $id = (int) $row['id'];
            $attempts = (int) $row['attempts'];
            $firstAttemptAt = (int) $row['first_attempt_at'];
            $lockedUntil = $row['locked_until'] !== null ? (int) $row['locked_until'] : null;

            if ($lockedUntil !== null && $lockedUntil > $now) {
                if ($ownTx) {
                    $pdo->commit();
                }
                return false;
            }

            if (($now - $firstAttemptAt) > $windowSeconds) {
                $newAttempts = 1;
                $newLockedUntil = ($maxAttempts <= 1) ? ($now + $windowSeconds) : null;

                $upd = $pdo->prepare(
                    'UPDATE rate_limits
                     SET attempts = :attempts, first_attempt_at = :first_at, locked_until = :locked_until
                     WHERE id = :id'
                );
                $upd->execute([
                    ':attempts' => $newAttempts,
                    ':first_at' => $now,
                    ':locked_until' => $newLockedUntil,
                    ':id' => $id,
                ]);

                if ($ownTx) {
                    $pdo->commit();
                }
                return true;
            }

            if ($attempts >= $maxAttempts) {
                $upd = $pdo->prepare('UPDATE rate_limits SET locked_until = :locked_until WHERE id = :id');
                $upd->execute([
                    ':locked_until' => $now + $windowSeconds,
                    ':id' => $id,
                ]);

                if ($ownTx) {
                    $pdo->commit();
                }
                return false;
            }

            $newAttempts = $attempts + 1;
            $newLockedUntil = ($newAttempts >= $maxAttempts) ? ($now + $windowSeconds) : null;

            $upd = $pdo->prepare(
                'UPDATE rate_limits SET attempts = :attempts, locked_until = :locked_until WHERE id = :id'
            );
            $upd->execute([
                ':attempts' => $newAttempts,
                ':locked_until' => $newLockedUntil,
                ':id' => $id,
            ]);

            if ($ownTx) {
                $pdo->commit();
            }
            return true;
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                try {
                    $pdo->rollBack();
                } catch (Throwable) {
                }
            }
            SecurityService::logError('RateLimiter::attempt DB fallback: ' . $e->getMessage());
            return self::attemptFile($bucketKey, $maxAttempts, $windowSeconds, $now);
        }
    }

    /**
     * Atomically record one attempt (e.g., failed admin login) using INSERT ... ON DUPLICATE KEY UPDATE.
     */
    public static function hit(string $bucketKey, int $maxAttempts, int $windowSeconds): bool
    {
        $now = time();
        try {
            $pdo = Connection::getPdo();
            $initialLock = ($maxAttempts <= 1) ? ($now + $windowSeconds) : null;

            $sql = 'INSERT INTO rate_limits (bucket_key, attempts, first_attempt_at, locked_until)
                    VALUES (:key, 1, :now_ins, :lock_ins)
                    ON DUPLICATE KEY UPDATE
                      attempts = IF(
                        (locked_until IS NULL OR locked_until <= :now_u1) AND (:now_u2 - :window_u1) > CAST(first_attempt_at AS SIGNED),
                        1,
                        attempts + 1
                      ),
                      first_attempt_at = IF(
                        (locked_until IS NULL OR locked_until <= :now_u3) AND (:now_u4 - :window_u2) > CAST(first_attempt_at AS SIGNED),
                        :now_u5,
                        first_attempt_at
                      ),
                      locked_until = IF(
                        locked_until IS NOT NULL AND locked_until > :now_u6,
                        locked_until,
                        IF(attempts >= :max_u, :now_u7 + :window_u3, NULL)
                      )';

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':key' => $bucketKey,
                ':now_ins' => $now,
                ':lock_ins' => $initialLock,
                ':now_u1' => $now,
                ':now_u2' => $now,
                ':window_u1' => $windowSeconds,
                ':now_u3' => $now,
                ':now_u4' => $now,
                ':window_u2' => $windowSeconds,
                ':now_u5' => $now,
                ':now_u6' => $now,
                ':max_u' => $maxAttempts,
                ':now_u7' => $now,
                ':window_u3' => $windowSeconds,
            ]);
            return true;
        } catch (Throwable $e) {
            SecurityService::logError('RateLimiter::hit DB fallback: ' . $e->getMessage());
            return self::hitFile($bucketKey, $maxAttempts, $windowSeconds, $now);
        }
    }

    public static function clear(string $bucketKey): void
    {
        try {
            $pdo = Connection::getPdo();
            $stmt = $pdo->prepare('DELETE FROM rate_limits WHERE bucket_key = :key');
            $stmt->execute([':key' => $bucketKey]);
        } catch (Throwable $e) {
            SecurityService::logError('RateLimiter::clear DB fallback: ' . $e->getMessage());
            $path = self::filePath($bucketKey);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private static function fileDir(): string
    {
        return STORAGE_PATH . '/ratelimit';
    }

    private static function filePath(string $bucketKey): string
    {
        return self::fileDir() . '/' . hash('sha256', $bucketKey) . '.json';
    }

    private static function ensureFileStorageDir(): bool
    {
        $dir = self::fileDir();
        if (!is_dir($dir) && !@mkdir($dir, 0750, true) && !is_dir($dir)) {
            SecurityService::logError('RateLimiter file storage directory cannot be created.');
            return false;
        }
        if (!is_writable($dir)) {
            SecurityService::logError('RateLimiter file storage directory is not writable.');
            return false;
        }
        return true;
    }

    private static function isBlockedFile(string $bucketKey, int $maxAttempts, int $windowSeconds, int $now): bool
    {
        if (!self::ensureFileStorageDir()) {
            return true; // Fail closed
        }

        $path = self::filePath($bucketKey);
        if (!is_file($path)) {
            return false;
        }

        $fp = @fopen($path, 'rb');
        if ($fp === false) {
            SecurityService::logError('RateLimiter::isBlockedFile failed to open bucket file.');
            return true; // Fail closed
        }

        try {
            if (!flock($fp, LOCK_SH)) {
                SecurityService::logError('RateLimiter::isBlockedFile failed to acquire shared lock.');
                return true; // Fail closed
            }

            $raw = stream_get_contents($fp);
            if ($raw === false) {
                SecurityService::logError('RateLimiter::isBlockedFile failed to read bucket file.');
                return true; // Fail closed
            }
            if (trim($raw) === '') {
                return false;
            }

            $data = json_decode($raw, true);
            if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
                SecurityService::logError('RateLimiter::isBlockedFile encountered corrupted bucket JSON.');
                return true; // Fail closed
            }

            if (!empty($data['locked_until']) && (int) $data['locked_until'] > $now) {
                return true;
            }
            if (($now - (int) ($data['first_attempt_at'] ?? 0)) > $windowSeconds) {
                return false;
            }
            return (int) ($data['attempts'] ?? 0) >= $maxAttempts;
        } finally {
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }

    private static function attemptFile(string $bucketKey, int $maxAttempts, int $windowSeconds, int $now): bool
    {
        if (!self::ensureFileStorageDir()) {
            return false; // Fail closed
        }

        $path = self::filePath($bucketKey);
        $fp = @fopen($path, 'c+b');
        if ($fp === false) {
            SecurityService::logError('RateLimiter::attemptFile failed to open bucket file.');
            return false; // Fail closed
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                SecurityService::logError('RateLimiter::attemptFile failed to acquire exclusive lock.');
                return false; // Fail closed
            }

            $raw = stream_get_contents($fp);
            if ($raw === false) {
                SecurityService::logError('RateLimiter::attemptFile failed to read bucket file.');
                return false; // Fail closed
            }

            $isNewOrEmpty = (trim($raw) === '');
            $data = null;
            if (!$isNewOrEmpty) {
                $decoded = json_decode($raw, true);
                if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                    SecurityService::logError('RateLimiter::attemptFile encountered corrupted bucket JSON.');
                    return false; // Fail closed without overwriting corrupted data
                }
                $data = $decoded;
            }

            if ($isNewOrEmpty) {
                $data = [
                    'attempts' => 1,
                    'first_attempt_at' => $now,
                    'locked_until' => ($maxAttempts <= 1) ? ($now + $windowSeconds) : null,
                ];
                $allowed = true;
            } elseif (!empty($data['locked_until']) && (int) $data['locked_until'] > $now) {
                return false;
            } elseif (($now - (int) ($data['first_attempt_at'] ?? 0)) > $windowSeconds) {
                $data = [
                    'attempts' => 1,
                    'first_attempt_at' => $now,
                    'locked_until' => ($maxAttempts <= 1) ? ($now + $windowSeconds) : null,
                ];
                $allowed = true;
            } elseif ((int) ($data['attempts'] ?? 0) >= $maxAttempts) {
                $data['locked_until'] = $now + $windowSeconds;
                $allowed = false;
            } else {
                $newAttempts = (int) ($data['attempts'] ?? 0) + 1;
                $data['attempts'] = $newAttempts;
                $data['locked_until'] = ($newAttempts >= $maxAttempts) ? ($now + $windowSeconds) : null;
                $allowed = true;
            }

            if (!self::writeLockedJson($fp, $data)) {
                SecurityService::logError('RateLimiter::attemptFile failed to write bucket file.');
                return false; // Fail closed
            }

            return $allowed;
        } finally {
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }

    private static function hitFile(string $bucketKey, int $maxAttempts, int $windowSeconds, int $now): bool
    {
        if (!self::ensureFileStorageDir()) {
            return false; // Fail closed
        }

        $path = self::filePath($bucketKey);
        $fp = @fopen($path, 'c+b');
        if ($fp === false) {
            SecurityService::logError('RateLimiter::hitFile failed to open bucket file.');
            return false;
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                SecurityService::logError('RateLimiter::hitFile failed to acquire exclusive lock.');
                return false;
            }

            $raw = stream_get_contents($fp);
            if ($raw === false) {
                SecurityService::logError('RateLimiter::hitFile failed to read bucket file.');
                return false; // Fail closed
            }

            $isNewOrEmpty = (trim($raw) === '');
            $data = null;
            if (!$isNewOrEmpty) {
                $decoded = json_decode($raw, true);
                if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                    SecurityService::logError('RateLimiter::hitFile encountered corrupted bucket JSON.');
                    return false; // Fail closed without overwriting corrupted data
                }
                $data = $decoded;
            }

            $hasActiveLock = !$isNewOrEmpty && is_array($data) && !empty($data['locked_until']) && (int) $data['locked_until'] > $now;

            if ($isNewOrEmpty || (!$hasActiveLock && ($now - (int) ($data['first_attempt_at'] ?? 0)) > $windowSeconds)) {
                $data = [
                    'attempts' => 1,
                    'first_attempt_at' => $now,
                    'locked_until' => ($maxAttempts <= 1) ? ($now + $windowSeconds) : null,
                ];
            } else {
                $data['attempts'] = (int) ($data['attempts'] ?? 0) + 1;
                if (!$hasActiveLock && $data['attempts'] >= $maxAttempts) {
                    $data['locked_until'] = $now + $windowSeconds;
                }
            }

            if (!self::writeLockedJson($fp, $data)) {
                SecurityService::logError('RateLimiter::hitFile failed to write bucket file.');
                return false;
            }

            return true;
        } finally {
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }

    /**
     * @param resource $fp
     */
    private static function writeLockedJson($fp, array $data): bool
    {
        $encoded = json_encode($data);
        if ($encoded === false) {
            return false;
        }
        if (!rewind($fp) || !ftruncate($fp, 0)) {
            return false;
        }
        $written = fwrite($fp, $encoded);
        fflush($fp);
        return $written !== false && $written === strlen($encoded);
    }
}
