<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEW_PATH', BASE_PATH . '/app/views');

// 1. Load .env securely (check parent directory above document root first, then BASE_PATH)
$envCandidates = [
    dirname(BASE_PATH) . '/.env',
    BASE_PATH . '/.env',
];

foreach ($envCandidates as $envFile) {
    if (is_file($envFile) && is_readable($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines !== false) {
            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                    continue;
                }
                $eqPos = strpos($trimmed, '=');
                if ($eqPos === false) {
                    continue;
                }
                $key = trim(substr($trimmed, 0, $eqPos));
                $val = trim(substr($trimmed, $eqPos + 1));
                if (
                    (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))
                ) {
                    $val = substr($val, 1, -1);
                }
                if ($key !== '' && !array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $val;
                    $_SERVER[$key] = $val;
                }
            }
        }
        break;
    }
}

$GLOBALS['app_config'] = require BASE_PATH . '/app/config/config.php';

// 2. Load core classes
require_once BASE_PATH . '/app/services/SecurityService.php';
require_once BASE_PATH . '/app/database/Connection.php';
require_once BASE_PATH . '/app/middleware/CsrfMiddleware.php';
require_once BASE_PATH . '/app/middleware/AuthMiddleware.php';
require_once BASE_PATH . '/app/services/RateLimiter.php';
require_once BASE_PATH . '/app/services/ValidationService.php';
require_once BASE_PATH . '/app/services/BriefService.php';
require_once BASE_PATH . '/app/services/AuthService.php';
require_once BASE_PATH . '/app/controllers/PublicBriefController.php';
require_once BASE_PATH . '/app/controllers/AdminController.php';

// 3. Configure safe error logging without leaking internals
ini_set('display_errors', '0');
ini_set('log_errors', '1');
if (is_dir(STORAGE_PATH . '/logs') && is_writable(STORAGE_PATH . '/logs')) {
    ini_set('error_log', STORAGE_PATH . '/logs/php-error.log');
}

set_exception_handler(static function (Throwable $e): void {
    SecurityService::logError('Unhandled Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "Error: Terjadi kesalahan sistem.\n");
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Gangguan Layanan — ArchonDigital</title>';
    echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body class="bg-light d-flex align-items-center min-vh-100">';
    echo '<main class="container"><div class="row justify-content-center"><div class="col-md-7 col-lg-6"><div class="card border-0 shadow-sm p-4 p-md-5">';
    echo '<h1 class="h4 fw-bold text-dark mb-3">Layanan Sedang Mengalami Kendala Teknis</h1>';
    echo '<p class="text-secondary mb-4">Permintaan Anda belum dapat diproses saat ini. Silakan coba kembali beberapa saat lagi atau hubungi ArchonDigital langsung melalui WhatsApp.</p>';
    echo '<div class="d-flex flex-wrap gap-2"><a class="btn btn-dark rounded-pill px-4" href="/">Kembali ke Halaman Utama</a>';
    echo '<a class="btn btn-outline-secondary rounded-pill px-4" href="https://wa.me/6285159117451" target="_blank" rel="noopener noreferrer">Hubungi WhatsApp</a></div>';
    echo '</div></div></div></main></body></html>';
    exit;
});

// 4. Web-only initialization (headers & secure session)
if (PHP_SAPI !== 'cli') {
    date_default_timezone_set('Asia/Jakarta');
    SecurityService::sendSecurityHeaders();
    SecurityService::startSecureSession(app_config('session'));

    $scriptName = (string) ($_SERVER['SCRIPT_NAME'] ?? '');
    $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    if (preg_match('#(?:^|/)admin(?:/|$)#', $scriptName) === 1 || preg_match('#(?:^|/)admin(?:/|$)#', $requestUri) === 1) {
        SecurityService::sendNoCacheHeaders();
    }
}

/**
 * Global helper to read application configuration.
 */
function app_config(?string $key = null): mixed
{
    $cfg = $GLOBALS['app_config'] ?? [];
    if ($key === null) {
        return $cfg;
    }
    return $cfg[$key] ?? null;
}

/**
 * Global HTML escaping helper.
 */
function e(?string $value): string
{
    return SecurityService::escape($value);
}

/**
 * Global view renderer.
 */
function render_view(string $view, array $data = []): void
{
    $file = VIEW_PATH . '/' . ltrim($view, '/') . '.php';
    if (!is_file($file)) {
        throw new RuntimeException('View tidak ditemukan.');
    }
    extract($data, EXTR_SKIP);
    require $file;
}
