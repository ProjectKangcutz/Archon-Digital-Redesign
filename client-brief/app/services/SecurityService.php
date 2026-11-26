<?php
declare(strict_types=1);

final class SecurityService
{
    public static function escape(?string $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }
        if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }
        return false;
    }

    public static function sendSecurityHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        header(
            "Content-Security-Policy: default-src 'self'; " .
            "style-src 'self' https://cdn.jsdelivr.net https://fonts.googleapis.com 'unsafe-inline'; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "script-src 'self' https://cdn.jsdelivr.net; " .
            "img-src 'self' data:; " .
            "form-action 'self'; " .
            "frame-ancestors 'none'; " .
            "base-uri 'self';"
        );

        if (self::isHttps()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }

    public static function sendNoCacheHeaders(): void
    {
        if (headers_sent()) {
            return;
        }

        header('Cache-Control: no-store, private, max-age=0', true);
        header('Pragma: no-cache', true);
        header('Expires: 0', true);
    }

    public static function startSecureSession(array $sessionConfig): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $isHttps = self::isHttps();
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');

        session_name($sessionConfig['name'] ?? 'archon_brief_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    public static function getClientIpHash(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        return hash('sha256', $ip . '|archon-client-brief');
    }

    public static function sanitizeReferenceUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }
        $trimmed = trim($url);
        $parts = parse_url($trimmed);
        if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
            return null;
        }
        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }
        return $trimmed;
    }

    public static function buildSafeWhatsappUrl(string $phoneDigits, string $message): string
    {
        $cleanDigits = preg_replace('/[^0-9]/', '', $phoneDigits) ?? '';
        if ($cleanDigits === '') {
            $cleanDigits = (string) app_config('archon_whatsapp');
        }
        return 'https://wa.me/' . $cleanDigits . '?text=' . rawurlencode($message);
    }

    public static function logError(string $message): void
    {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir) || !is_writable($logDir)) {
            return;
        }
        $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), preg_replace('/[\r\n]+/', ' ', $message));
        @file_put_contents($logDir . '/app.log', $line, FILE_APPEND | LOCK_EX);
    }
}
