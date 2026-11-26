<?php
declare(strict_types=1);

final class AuthService
{
    // Dummy bcrypt hash used to equalize timing when email does not exist
    private const DUMMY_HASH = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    public static function attemptLogin(string $email, string $password): array
    {
        $cleanEmail = strtolower(trim($email));
        $rlCfg = app_config('rate_limit');
        $maxAttempts = (int) ($rlCfg['login_max_attempts'] ?? 5);
        $windowSeconds = (int) ($rlCfg['login_window_seconds'] ?? 900);

        $ipBucket = 'login_ip:' . SecurityService::getClientIpHash();
        $emailBucket = 'login_email:' . hash('sha256', $cleanEmail);

        if (
            RateLimiter::isBlocked($ipBucket, $maxAttempts, $windowSeconds) ||
            ($cleanEmail !== '' && RateLimiter::isBlocked($emailBucket, $maxAttempts, $windowSeconds))
        ) {
            return [
                'ok' => false,
                'error' => 'Terlalu banyak percobaan masuk yang gagal. Silakan tunggu sekitar 15 menit sebelum mencoba lagi.',
            ];
        }

        if ($cleanEmail === '' || $password === '' || filter_var($cleanEmail, FILTER_VALIDATE_EMAIL) === false) {
            if (!RateLimiter::hit($ipBucket, $maxAttempts, $windowSeconds)) {
                SecurityService::logError('AuthService::attemptLogin stopped because IP rate-limit storage failed.');
                return [
                    'ok' => false,
                    'error' => 'Layanan autentikasi sedang mengalami gangguan sementara. Silakan coba kembali beberapa saat lagi.',
                ];
            }
            return [
                'ok' => false,
                'error' => 'Email atau kata sandi tidak valid.',
            ];
        }

        $pdo = Connection::getPdo();
        $stmt = $pdo->prepare('SELECT id, name, email, password_hash FROM admins WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $cleanEmail]);
        $admin = $stmt->fetch();

        $hashToVerify = $admin ? (string) $admin['password_hash'] : self::DUMMY_HASH;
        $passwordOk = password_verify($password, $hashToVerify);

        if (!$admin || !$passwordOk) {
            $ipHitOk = RateLimiter::hit($ipBucket, $maxAttempts, $windowSeconds);
            $emailHitOk = RateLimiter::hit($emailBucket, $maxAttempts, $windowSeconds);
            if (!$ipHitOk || !$emailHitOk) {
                SecurityService::logError('AuthService::attemptLogin stopped because rate-limit storage failed.');
                return [
                    'ok' => false,
                    'error' => 'Layanan autentikasi sedang mengalami gangguan sementara. Silakan coba kembali beberapa saat lagi.',
                ];
            }
            return [
                'ok' => false,
                'error' => 'Email atau kata sandi tidak valid.',
            ];
        }

        // Clear rate-limit counters on successful login
        RateLimiter::clear($ipBucket);
        RateLimiter::clear($emailBucket);

        // Rehash if algorithm/cost upgraded
        if (password_needs_rehash($hashToVerify, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $rehashStmt = $pdo->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
            $rehashStmt->execute([':hash' => $newHash, ':id' => (int) $admin['id']]);
        }

        $updStmt = $pdo->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = :id');
        $updStmt->execute([':id' => (int) $admin['id']]);

        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        CsrfMiddleware::rotate();

        $now = time();
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_name'] = (string) $admin['name'];
        $_SESSION['admin_email'] = (string) $admin['email'];
        $_SESSION['admin_logged_in_at'] = $now;
        $_SESSION['admin_last_activity'] = $now;

        return ['ok' => true];
    }

    public static function logout(): void
    {
        AuthMiddleware::clearSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'] ?? '/',
                    'domain' => $params['domain'] ?? '',
                    'secure' => (bool) ($params['secure'] ?? false),
                    'httponly' => true,
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        session_destroy();
    }
}
