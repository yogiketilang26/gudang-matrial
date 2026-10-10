<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/riwayat_query.php';

$page = 'riwayat';
$title = 'Riwayat stok';
$f = riwayat_filters();

try {
    $pdo = db();
    $gudangs = $pdo->query('SELECT DISTINCT gudang FROM riwayat ORDER BY gudang')->fetchAll(PDO::FETCH_COLUMN);
    [$sql, $params] = riwayat_sql($f);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $err) {
    $gudangs = [];
    $rows = [];
}

$qs = http_build_query(array_filter($f, static fn($v) => $v !== ''));

require __DIR__ . '/includes/header.php';
?>

<h1>Riwayat barang masuk &amp; keluar</h1>
<p class="lead">Setiap transaksi tersimpan. Filter lalu ekspor ke Excel atau PDF.</p>

<section class="card">
    <form class="filters" method="get">
        <div>
            <label>Cari</label>
            <input type="search" name="q" value="<?= e($f['q']) ?>" placeholder="Nama, kode, keterangan">
        </div>
        <div>
            <label>Tipe</label>
            <select name="tipe">
                <option value="">Semua</option>
                <option value="IN" <?= $f['tipe'] === 'IN' ? 'selected' : '' ?>>Stock in</option>
                <option value="OUT" <?= $f['tipe'] === 'OUT' ? 'selected' : '' ?>>Stock out</option>
            </select>
        </div>
        <div>
            <label>Gudang / ruangan</label>
            <select name="gudang">
                <option value="">Semua</option>
                <?php foreach ($gudangs as $g): ?>
                    <option value="<?= e($g) ?>" <?= $f['gudang'] === $g ? 'selected' : '' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Dari tanggal</label>
            <input type="date" name="dari" value="<?= e($f['dari']) ?>">
        </div>
        <div>
            <label>Sampai tanggal</label>
            <input type="date" name="sampai" value="<?= e($f['sampai']) ?>">
        </div>
        <div class="actions" style="align-self:end">
            <button class="btn" type="submit">Terapkan</button>
            <a class="btn ghost" href="riwayat.php">Reset</a>
        </div>
    </form>

    <div class="actions" style="margin-bottom:14px">
        <a class="btn secondary" href="export.php?format=excel&amp;<?= e($qs) ?>">Ekspor Excel</a>
        <a class="btn secondary" href="export.php?format=pdf&amp;<?= e($qs) ?>">Ekspor PDF</a>
    </div>

    <div class="table-wrap">
        <?php if (!$rows): ?>
            <div class="empty">Belum ada riwayat untuk filter ini.</div>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kode</th>
                    <th>Nama barang/material</th>
                    <th>Kategori</th>
                    <th>Gudang / ruangan</th>
                    <th>Jumlah</th>
                    <th>Stok sebelum</th>
                    <th>Stok sesudah</th>
                    <th>Keterangan</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['tanggal']) ?></td>
                        <td>
                            <span class="badge <?= $row['tipe'] === 'IN' ? 'in' : 'out' ?>">
                                <?= $row['tipe'] === 'IN' ? 'IN' : 'OUT' ?>
                            </span>
                        </td>
                        <td><?= e($row['kode'] ?: '-') ?></td>
                        <td><?= e($row['nama']) ?></td>
                        <td><?= e($row['kategori'] ?: '-') ?></td>
                        <td><?= e($row['gudang']) ?></td>
                        <td><?= (int) $row['jumlah'] ?></td>
                        <td><?= (int) $row['stok_sebelum'] ?></td>
                        <td><?= (int) $row['stok_sesudah'] ?></td>
                        <td><?= e($row['keterangan'] ?: '-') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
