<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/riwayat_query.php';

$format = $_GET['format'] ?? 'excel';
$f = riwayat_filters();

try {
    $pdo = db();
    [$sql, $params] = riwayat_sql($f);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
} catch (Throwable $err) {
    http_response_code(500);
    echo 'Gagal mengambil data: ' . e($err->getMessage());
    exit;
}

function tipe_label(string $tipe): string
{
    return $tipe === 'IN' ? 'Stock In' : 'Stock Out';
}

if ($format === 'excel') {
    $filename = 'riwayat-stok-' . date('Ymd-His') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo "\xEF\xBB\xBF";
    echo '<table border="1">';
    echo '<tr><th colspan="10">Riwayat Barang Masuk &amp; Keluar — ' . e(APP_NAME) . '</th></tr>';
    echo '<tr>
        <th>Tanggal</th><th>Tipe</th><th>Kode</th><th>Nama barang/material</th>
        <th>Kategori</th><th>Gudang/Ruangan</th><th>Jumlah</th>
        <th>Stok sebelum</th><th>Stok sesudah</th><th>Keterangan</th>
    </tr>';
    foreach ($rows as $row) {
        echo '<tr>';
        echo '<td>' . e($row['tanggal']) . '</td>';
        echo '<td>' . e(tipe_label($row['tipe'])) . '</td>';
        echo '<td>' . e((string) ($row['kode'] ?? '')) . '</td>';
        echo '<td>' . e($row['nama']) . '</td>';
        echo '<td>' . e((string) ($row['kategori'] ?? '')) . '</td>';
        echo '<td>' . e($row['gudang']) . '</td>';
        echo '<td>' . (int) $row['jumlah'] . '</td>';
        echo '<td>' . (int) $row['stok_sebelum'] . '</td>';
        echo '<td>' . (int) $row['stok_sesudah'] . '</td>';
        echo '<td>' . e((string) ($row['keterangan'] ?? '')) . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    exit;
}

$page = 'riwayat';
$title = 'Ekspor PDF riwayat';
require __DIR__ . '/includes/header.php';
?>
<section class="card print-area">
    <div class="toolbar no-print">
        <div>
            <h1>Pratinjau PDF</h1>
            <p class="lead">Klik unduh PDF, lalu pilih “Save as PDF” / “Microsoft Print to PDF”.</p>
        </div>
        <div class="actions">
            <button class="btn" type="button" data-print>Unduh PDF</button>
            <a class="btn ghost" href="riwayat.php?<?= e(http_build_query(array_filter($f))) ?>">Kembali</a>
        </div>
    </div>

    <h2>Riwayat Barang Masuk &amp; Keluar</h2>
    <p>Dicetak: <?= e(date('d-m-Y H:i')) ?> · <?= count($rows) ?> transaksi</p>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Kode</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Gudang</th>
                <th>Jumlah</th>
                <th>Sebelum</th>
                <th>Sesudah</th>
                <th>Keterangan</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$rows): ?>
                <tr><td colspan="10">Tidak ada data.</td></tr>
            <?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row['tanggal']) ?></td>
                    <td><?= e(tipe_label($row['tipe'])) ?></td>
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
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
