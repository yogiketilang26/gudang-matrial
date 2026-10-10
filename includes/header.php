<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$page = $page ?? 'dashboard';
$title = $title ?? APP_NAME;
$flash = flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> — <?= e(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand">
        <span class="logo">SG</span>
        <div>
            <strong><?= e(APP_NAME) ?></strong>
            <small>Pengelolaan barang &amp; material</small>
        </div>
    </div>
    <nav>
        <a class="<?= $page === 'dashboard' ? 'active' : '' ?>" href="index.php">Stok</a>
        <a class="<?= $page === 'masuk' ? 'active' : '' ?>" href="masuk.php">Barang Masuk</a>
        <a class="<?= $page === 'keluar' ? 'active' : '' ?>" href="keluar.php">Barang Keluar</a>
        <a class="<?= $page === 'riwayat' ? 'active' : '' ?>" href="riwayat.php">Riwayat</a>
    </nav>
</header>
<main class="wrap">
<?php if ($flash): ?>
    <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
