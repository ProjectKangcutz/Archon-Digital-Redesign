#!/usr/bin/env php
<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden: CLI execution only.');
}

require_once dirname(__DIR__) . '/app/bootstrap.php';

echo "=== Pembuatan Akun Admin Pertama — ArchonDigital Client Brief ===\n\n";

$name = trim((string) readline('Nama Admin: '));
$email = strtolower(trim((string) readline('Email Admin: ')));

echo "Password Admin (minimal 12 karakter disarankan): ";
if (function_exists('exec') && stripos(PHP_OS, 'WIN') !== 0) {
    @exec('stty -echo');
    $password = trim((string) fgets(STDIN));
    @exec('stty echo');
    echo "\n";
} else {
    $password = trim((string) fgets(STDIN));
}

if ($name === '' || mb_strlen($name) < 2) {
    fwrite(STDERR, "Error: Nama admin tidak valid.\n");
    exit(1);
}

if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "Error: Format email tidak valid.\n");
    exit(1);
}

if (mb_strlen($password) < 10) {
    fwrite(STDERR, "Error: Password wajib minimal 10 karakter.\n");
    exit(1);
}

$pdo = Connection::getPdo();
$check = $pdo->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
$check->execute([':email' => $email]);
if ($check->fetchColumn()) {
    fwrite(STDERR, "Error: Akun admin dengan email tersebut sudah terdaftar.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash) VALUES (:name, :email, :hash)');
$stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':hash' => $hash,
]);

echo "Berhasil: Akun admin '{$email}' telah dibuat dengan password hash yang aman.\n";
