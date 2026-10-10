<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flash(?string $message = null, string $type = 'ok'): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }

    $data = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $data;
}
