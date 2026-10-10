<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$done = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $sql = file_get_contents(__DIR__ . '/database.sql');
        if ($sql === false) {
            throw new RuntimeException('File database.sql tidak ditemukan.');
        }
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt !== '') {
                $pdo->exec($stmt);
            }
        }
        $done = true;
    } catch (Throwable $err) {
        $error = $err->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install — <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<main class="wrap">
    <section class="card">
        <h1>Install database</h1>
        <p class="lead">Pastikan MySQL/MariaDB menyala dan isi <code>config.php</code> sudah benar (user, password).</p>
        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($done): ?>
            <div class="alert ok">Database siap. Silakan mulai mencatat stok.</div>
            <p><a class="btn" href="index.php">Buka aplikasi</a></p>
        <?php else: ?>
            <form method="post">
                <button class="btn" type="submit">Buat database &amp; tabel</button>
            </form>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
