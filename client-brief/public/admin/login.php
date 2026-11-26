<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

$controller = new AdminController();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $controller->handleLogin();
} else {
    $controller->showLogin();
}
