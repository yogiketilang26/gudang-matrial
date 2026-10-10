<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$page = 'dashboard';
$title = 'Stok barang';

$q = trim((string) ($_GET['q'] ?? ''));
$gudang = trim((string) ($_GET['gudang'] ?? ''));

try {
    $pdo = db();
    $stats = [
        'barang' => (int) $pdo->query('SELECT COUNT(*) FROM barang')->fetchColumn(),
        'stok' => (int) $pdo->query('SELECT COALESCE(SUM(stok),0) FROM barang')->fetchColumn(),
        'in' => (int) $pdo->query("SELECT COUNT(*) FROM riwayat WHERE tipe='IN'")->fetchColumn(),
        'out' => (int) $pdo->query("SELECT COUNT(*) FROM riwayat WHERE tipe='OUT'")->fetchColumn(),
    ];
    $gudangs = $pdo->query('SELECT DISTINCT gudang FROM barang ORDER BY gudang')->fetchAll(PDO::FETCH_COLUMN);

    $sql = 'SELECT * FROM barang WHERE 1=1';
    $params = [];
    if ($q !== '') {
        $sql .= ' AND (nama LIKE ? OR kode LIKE ? OR kategori LIKE ?)';
        $like = '%' . $q . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    if ($gudang !== '') {
        $sql .= ' AND gudang = ?';
        $params[] = $gudang;
    }
    $sql .= ' ORDER BY nama, gudang';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $err) {
    $setupError = $err->getMessage();
    $stats = ['barang' => 0, 'stok' => 0, 'in' => 0, 'out' => 0];
    $gudangs = [];
    $rows = [];
}

require __DIR__ . '/includes/header.php';
?>

<h1>Stok terkini</h1>
<p class="lead">Lihat sisa barang/material per gudang atau ruangan.</p>

<?php if (!empty($setupError)): ?>
    <div class="alert error">
        Database belum siap: <?= e($setupError) ?>.
        Impor file <code>database.sql</code> di phpMyAdmin, lalu sesuaikan <code>config.php</code>.
    </div>
<?php endif; ?>

<section class="stats">
    <div class="stat"><span>Jenis barang</span><strong><?= (int) $stats['barang'] ?></strong></div>
    <div class="stat"><span>Total stok</span><strong><?= (int) $stats['stok'] ?></strong></div>
    <div class="stat"><span>Riwayat masuk</span><strong><?= (int) $stats['in'] ?></strong></div>
    <div class="stat"><span>Riwayat keluar</span><strong><?= (int) $stats['out'] ?></strong></div>
</section>

<section class="card">
    <form class="filters" method="get">
        <div>
            <label>Cari nama / kode / kategori</label>
            <input type="search" name="q" value="<?= e($q) ?>" placeholder="Contoh: semen, KBL-01">
        </div>
        <div>
            <label>Gudang / ruangan</label>
            <select name="gudang">
                <option value="">Semua</option>
                <?php foreach ($gudangs as $g): ?>
                    <option value="<?= e($g) ?>" <?= $gudang === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="actions" style="align-self:end">
            <button class="btn" type="submit">Filter</button>
            <a class="btn ghost" href="index.php">Reset</a>
        </div>
    </form>

    <div class="table-wrap">
        <?php if (!$rows): ?>
            <div class="empty">Belum ada data stok. Mulai dari menu Barang Masuk.</div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama barang/material</th>
                    <th>Kategori</th>
                    <th>Gudang / ruangan</th>
                    <th>Stok</th>
                    <th>Keterangan</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['kode'] ?: '-') ?></td>
                        <td><?= e($row['nama']) ?></td>
                        <td><?= e($row['kategori'] ?: '-') ?></td>
                        <td><?= e($row['gudang']) ?></td>
                        <td class="<?= (int) $row['stok'] <= 0 ? 'low' : '' ?>"><?= (int) $row['stok'] ?></td>
                        <td><?= e($row['keterangan'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
