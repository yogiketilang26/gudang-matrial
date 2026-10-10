<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$page = 'keluar';
$title = 'Barang keluar';

try {
    $pdo = db();
    $barang = $pdo->query('SELECT id, kode, nama, gudang, stok FROM barang WHERE stok > 0 ORDER BY nama')->fetchAll();
} catch (Throwable $err) {
    $barang = [];
}

require __DIR__ . '/includes/header.php';
?>

<h1>Barang keluar</h1>
<p class="lead">Setiap stok keluar tercatat di riwayat beserta tanggal keluar dan sisa stok.</p>

<section class="card">
    <form method="post" action="aksi.php" class="form-grid">
        <input type="hidden" name="aksi" value="keluar">
        <div>
            <label>Tanggal keluar</label>
            <input type="date" name="tanggal_keluar" data-today required>
        </div>
        <div>
            <label>Nama barang / material</label>
            <select name="barang_id" id="barang_id" required>
                <option value="">— Pilih barang —</option>
                <?php foreach ($barang as $b): ?>
                    <option value="<?= (int) $b['id'] ?>" data-stok="<?= (int) $b['stok'] ?>">
                        <?= e(($b['kode'] ? $b['kode'] . ' · ' : '') . $b['nama'] . ' — ' . $b['gudang']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small id="stok-hint" style="color:#5d6b67"></small>
        </div>
        <div>
            <label>Stock out (jumlah keluar)</label>
            <input type="number" name="jumlah" id="jumlah" min="1" required>
        </div>
        <div class="span-2">
            <label>Keterangan</label>
            <textarea name="keterangan" placeholder="Keperluan, penerima, proyek, dll."></textarea>
        </div>
        <div class="span-2 actions">
            <button class="btn out" type="submit">Simpan barang keluar</button>
            <a class="btn ghost" href="riwayat.php">Lihat riwayat</a>
        </div>
    </form>
    <?php if (!$barang): ?>
        <p class="empty">Tidak ada stok yang bisa dikeluarkan. Catat barang masuk terlebih dahulu.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
