<?php
declare(strict_types=1);

final class RateLimiter
{
    public static function isBlocked(string $bucketKey, int $maxAttempts, int $windowSeconds): bool
    {
        try {
            $pdo = Connection::getPdo();
            $stmt = $pdo->prepare('SELECT attempts, first_attempt_at, locked_until FROM rate_limits WHERE bucket_key = :key LIMIT 1');
            $stmt->execute([':key' => $bucketKey]);
            $row = $stmt->fetch();

            if (!$row) {
                return false;
            }

            $now = time();
            if ($row['locked_until'] !== null && (int) $row['locked_until'] > $now) {
                return true;
            }

            if (($now - (int) $row['first_attempt_at']) > $windowSeconds) {
                return false;
            }

            return (int) $row['attempts'] >= $maxAttempts;
        } catch (Throwable $e) {
            return self::isBlockedFile($bucketKey, $maxAttempts, $windowSeconds);
        }
    }

    public static function hit(string $bucketKey, int $maxAttempts, int $windowSeconds): void
    {
        $now = time();
        try {
            $pdo = Connection::getPdo();
            $stmt = $pdo->prepare('SELECT id, attempts, first_attempt_at FROM rate_limits WHERE bucket_key = :key LIMIT 1');
            $stmt->execute([':key' => $bucketKey]);
            $row = $stmt->fetch();

            if (!$row) {
                $lockedUntil = ($maxAttempts <= 1) ? ($now + $windowSeconds) : null;
                $ins = $pdo->prepare(
                    'INSERT INTO rate_limits (bucket_key, attempts, first_attempt_at, locked_until) VALUES (:key, 1, :first_at, :locked_until)'
                );
                $ins->execute([
                    ':key' => $bucketKey,
                    ':first_at' => $now,
                    ':locked_until' => $lockedUntil,
                ]);
                return;
            }

            if (($now - (int) $row['first_attempt_at']) > $windowSeconds) {
                $upd = $pdo->prepare(
                    'UPDATE rate_limits SET attempts = 1, first_attempt_at = :first_at, locked_until = NULL WHERE id = :id'
                );
                $upd->execute([':first_at' => $now, ':id' => (int) $row['id']]);
                return;
            }

            $newAttempts = (int) $row['attempts'] + 1;
            $lockedUntil = ($newAttempts >= $maxAttempts) ? ($now + $windowSeconds) : null;
            $upd = $pdo->prepare(
                'UPDATE rate_limits SET attempts = :attempts, locked_until = :locked_until WHERE id = :id'
            );
            $upd->execute([
                ':attempts' => $newAttempts,
                ':locked_until' => $lockedUntil,
                ':id' => (int) $row['id'],
            ]);
        } catch (Throwable $e) {
            self::hitFile($bucketKey, $maxAttempts, $windowSeconds);
        }
    }

    public static function clear(string $bucketKey): void
    {
        try {
            $pdo = Connection::getPdo();
            $stmt = $pdo->prepare('DELETE FROM rate_limits WHERE bucket_key = :key');
            $stmt->execute([':key' => $bucketKey]);
        } catch (Throwable $e) {
            $path = self::filePath($bucketKey);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    private static function filePath(string $bucketKey): string
    {
        return STORAGE_PATH . '/ratelimit/' . hash('sha256', $bucketKey) . '.json';
    }

    private static function isBlockedFile(string $bucketKey, int $maxAttempts, int $windowSeconds): bool
    {
        $path = self::filePath($bucketKey);
        if (!is_file($path)) {
            return false;
        }
        $data = json_decode((string) @file_get_contents($path), true);
        if (!is_array($data)) {
            return false;
        }
        $now = time();
        if (!empty($data['locked_until']) && (int) $data['locked_until'] > $now) {
            return true;
        }
        if (($now - (int) ($data['first_attempt_at'] ?? 0)) > $windowSeconds) {
            return false;
        }
        return (int) ($data['attempts'] ?? 0) >= $maxAttempts;
    }

    private static function hitFile(string $bucketKey, int $maxAttempts, int $windowSeconds): void
    {
        $dir = STORAGE_PATH . '/ratelimit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $path = self::filePath($bucketKey);
        $now = time();
        $data = is_file($path) ? json_decode((string) @file_get_contents($path), true) : null;
        if (!is_array($data) || ($now - (int) ($data['first_attempt_at'] ?? 0)) > $windowSeconds) {
            $data = ['attempts' => 1, 'first_attempt_at' => $now, 'locked_until' => null];
        } else {
            $data['attempts'] = (int) $data['attempts'] + 1;
            if ($data['attempts'] >= $maxAttempts) {
                $data['locked_until'] = $now + $windowSeconds;
            }
        }
        @file_put_contents($path, json_encode($data), LOCK_EX);
    }
}
