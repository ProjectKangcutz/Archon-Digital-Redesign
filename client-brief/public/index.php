<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

$controller = new PublicBriefController();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $controller->handleSubmit();
} else {
    $controller->showForm();
}
