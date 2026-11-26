<?php
declare(strict_types=1);

return [
    'app_name' => $_ENV['APP_NAME'] ?? 'ArchonDigital Client Brief',
    'app_env' => $_ENV['APP_ENV'] ?? 'production',
    'app_url' => rtrim((string) ($_ENV['APP_URL'] ?? 'https://brief.archondigital.web.id'), '/'),
    'main_site_url' => (string) ($_ENV['MAIN_SITE_URL'] ?? 'https://archondigital.web.id/'),
    'archon_whatsapp' => (string) ($_ENV['ARCHON_WHATSAPP'] ?? '6285159117451'),

    'db' => [
        'host' => (string) ($_ENV['DB_HOST'] ?? '127.0.0.1'),
        'port' => (string) ($_ENV['DB_PORT'] ?? '3306'),
        'name' => (string) ($_ENV['DB_NAME'] ?? ''),
        'user' => (string) ($_ENV['DB_USER'] ?? ''),
        'pass' => (string) ($_ENV['DB_PASS'] ?? ''),
        'charset' => (string) ($_ENV['DB_CHARSET'] ?? 'utf8mb4'),
    ],

    'session' => [
        'name' => (string) ($_ENV['SESSION_NAME'] ?? 'archon_brief_session'),
        'idle_timeout' => max(300, (int) ($_ENV['SESSION_IDLE_TIMEOUT'] ?? 1800)),
        'absolute_timeout' => max(1800, (int) ($_ENV['SESSION_ABSOLUTE_TIMEOUT'] ?? 28800)),
    ],

    'rate_limit' => [
        'brief_max_attempts' => max(1, (int) ($_ENV['BRIEF_RATE_MAX_ATTEMPTS'] ?? 5)),
        'brief_window_seconds' => max(60, (int) ($_ENV['BRIEF_RATE_WINDOW_SECONDS'] ?? 900)),
        'login_max_attempts' => max(1, (int) ($_ENV['LOGIN_RATE_MAX_ATTEMPTS'] ?? 5)),
        'login_window_seconds' => max(60, (int) ($_ENV['LOGIN_RATE_WINDOW_SECONDS'] ?? 900)),
    ],
];
