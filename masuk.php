<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

$page = 'masuk';
$title = 'Barang masuk';

try {
    $pdo = db();
    $barang = $pdo->query('SELECT id, kode, nama, gudang, stok FROM barang ORDER BY nama')->fetchAll();
    $kategori = $pdo->query("SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori <> '' ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);
    $gudang = $pdo->query('SELECT DISTINCT gudang FROM barang ORDER BY gudang')->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $err) {
    $barang = [];
    $kategori = [];
    $gudang = [];
}

require __DIR__ . '/includes/header.php';
?>

<h1>Barang masuk</h1>
<p class="lead">Setiap stok masuk otomatis masuk ke riwayat. Isi barang baru, atau pilih barang yang sudah ada.</p>

<section class="card">
    <form method="post" action="aksi.php" class="form-grid">
        <input type="hidden" name="aksi" value="masuk">

        <div>
            <label>Tanggal masuk</label>
            <input type="date" name="tanggal" data-today required>
        </div>
        <div>
            <label>Pilih barang yang sudah ada (opsional)</label>
            <select name="barang_id">
                <option value="">— Barang baru —</option>
                <?php foreach ($barang as $b): ?>
                    <option value="<?= (int) $b['id'] ?>">
                        <?= e(($b['kode'] ? $b['kode'] . ' · ' : '') . $b['nama'] . ' (' . $b['gudang'] . ') stok ' . $b['stok']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div data-new-item>
            <label>Kode (opsional)</label>
            <input type="text" name="kode" maxlength="50" placeholder="Kosongkan jika tidak ada">
        </div>
        <div data-new-item>
            <label>Nama barang / material</label>
            <input type="text" name="nama" maxlength="180" placeholder="Wajib jika barang baru" list="nama-list">
            <datalist id="nama-list">
                <?php foreach ($barang as $b): ?>
                    <option value="<?= e($b['nama']) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>
        <div data-new-item>
            <label>Kategori (opsional)</label>
            <input type="text" name="kategori" maxlength="120" list="kategori-list">
            <datalist id="kategori-list">
                <?php foreach ($kategori as $k): ?>
                    <option value="<?= e($k) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>
        <div data-new-item>
            <label>Nama gudang / ruangan</label>
            <input type="text" name="gudang" maxlength="150" list="gudang-list" placeholder="Wajib jika barang baru">
            <datalist id="gudang-list">
                <?php foreach ($gudang as $g): ?>
                    <option value="<?= e($g) ?>"></option>
                <?php endforeach; ?>
            </datalist>
        </div>
        <div>
            <label>Stock in (jumlah masuk)</label>
            <input type="number" name="jumlah" min="1" required>
        </div>
        <div class="span-2">
            <label>Keterangan</label>
            <textarea name="keterangan" placeholder="Supplier, nomor PO, kondisi, dll."></textarea>
        </div>
        <div class="span-2 actions">
            <button class="btn" type="submit">Simpan barang masuk</button>
            <a class="btn ghost" href="riwayat.php">Lihat riwayat</a>
        </div>
    </form>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
