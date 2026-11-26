<?php
declare(strict_types=1);

final class AuthMiddleware
{
    public static function check(): bool
    {
        if (empty($_SESSION['admin_id']) || empty($_SESSION['admin_logged_in_at']) || empty($_SESSION['admin_last_activity'])) {
            return false;
        }

        $sessionCfg = app_config('session');
        $now = time();
        $idleLimit = (int) ($sessionCfg['idle_timeout'] ?? 1800);
        $absLimit = (int) ($sessionCfg['absolute_timeout'] ?? 28800);

        if (($now - (int) $_SESSION['admin_last_activity']) > $idleLimit) {
            self::clearSession();
            $_SESSION['auth_notice'] = 'Sesi Anda telah berakhir karena tidak ada aktivitas. Silakan masuk kembali.';
            return false;
        }

        if (($now - (int) $_SESSION['admin_logged_in_at']) > $absLimit) {
            self::clearSession();
            $_SESSION['auth_notice'] = 'Masa sesi maksimal telah tercapai. Silakan masuk kembali.';
            return false;
        }

        $_SESSION['admin_last_activity'] = $now;
        return true;
    }

    public static function requireAdmin(): array
    {
        if (!self::check()) {
            header('Location: /admin/login.php');
            exit;
        }

        return [
            'id' => (int) $_SESSION['admin_id'],
            'name' => (string) ($_SESSION['admin_name'] ?? 'Admin'),
            'email' => (string) ($_SESSION['admin_email'] ?? ''),
        ];
    }

    public static function clearSession(): void
    {
        unset(
            $_SESSION['admin_id'],
            $_SESSION['admin_name'],
            $_SESSION['admin_email'],
            $_SESSION['admin_logged_in_at'],
            $_SESSION['admin_last_activity']
        );
    }
}
