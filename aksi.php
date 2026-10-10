<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';

try {
    $pdo = db();

    if ($aksi === 'masuk') {
        $tanggal = trim((string) ($_POST['tanggal'] ?? ''));
        $kode = trim((string) ($_POST['kode'] ?? ''));
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $kategori = trim((string) ($_POST['kategori'] ?? ''));
        $gudang = trim((string) ($_POST['gudang'] ?? ''));
        $jumlah = (int) ($_POST['jumlah'] ?? 0);
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));
        $barangId = (int) ($_POST['barang_id'] ?? 0);

        if ($tanggal === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            throw new RuntimeException('Tanggal masuk wajib diisi.');
        }
        if ($jumlah < 1) {
            throw new RuntimeException('Jumlah stok masuk minimal 1.');
        }

        $pdo->beginTransaction();

        if ($barangId > 0) {
            $stmt = $pdo->prepare('SELECT * FROM barang WHERE id = ? FOR UPDATE');
            $stmt->execute([$barangId]);
            $barang = $stmt->fetch();
            if (!$barang) {
                throw new RuntimeException('Barang tidak ditemukan.');
            }
        } else {
            if ($nama === '' || $gudang === '') {
                throw new RuntimeException('Nama barang dan gudang/ruangan wajib diisi untuk barang baru.');
            }

            $kodeVal = $kode === '' ? null : $kode;
            if ($kodeVal !== null) {
                $cek = $pdo->prepare('SELECT id FROM barang WHERE kode = ?');
                $cek->execute([$kodeVal]);
                if ($cek->fetch()) {
                    throw new RuntimeException('Kode barang sudah dipakai. Pilih barang yang sudah ada atau gunakan kode lain.');
                }
            }

            $ins = $pdo->prepare(
                'INSERT INTO barang (kode, nama, kategori, gudang, stok, keterangan) VALUES (?, ?, ?, ?, 0, ?)'
            );
            $ins->execute([
                $kodeVal,
                $nama,
                $kategori === '' ? null : $kategori,
                $gudang,
                $keterangan === '' ? null : $keterangan,
            ]);
            $barangId = (int) $pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT * FROM barang WHERE id = ? FOR UPDATE');
            $stmt->execute([$barangId]);
            $barang = $stmt->fetch();
        }

        $sebelum = (int) $barang['stok'];
        $sesudah = $sebelum + $jumlah;
        $pdo->prepare('UPDATE barang SET stok = ?, keterangan = COALESCE(?, keterangan), updated_at = NOW() WHERE id = ?')
            ->execute([$sesudah, $keterangan === '' ? null : $keterangan, $barangId]);

        $pdo->prepare(
            'INSERT INTO riwayat (barang_id, tipe, tanggal, jumlah, stok_sebelum, stok_sesudah, kode, nama, kategori, gudang, keterangan)
             VALUES (?, "IN", ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $barangId,
            $tanggal,
            $jumlah,
            $sebelum,
            $sesudah,
            $barang['kode'],
            $barang['nama'],
            $barang['kategori'],
            $barang['gudang'],
            $keterangan === '' ? null : $keterangan,
        ]);

        $pdo->commit();
        flash('Barang masuk tercatat. Stok ' . $barang['nama'] . ' sekarang ' . $sesudah . '.');
        header('Location: riwayat.php');
        exit;
    }

    if ($aksi === 'keluar') {
        $tanggal = trim((string) ($_POST['tanggal_keluar'] ?? ''));
        $barangId = (int) ($_POST['barang_id'] ?? 0);
        $jumlah = (int) ($_POST['jumlah'] ?? 0);
        $keterangan = trim((string) ($_POST['keterangan'] ?? ''));

        if ($barangId < 1) {
            throw new RuntimeException('Pilih barang/material yang akan dikeluarkan.');
        }
        if ($tanggal === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            throw new RuntimeException('Tanggal keluar wajib diisi.');
        }
        if ($jumlah < 1) {
            throw new RuntimeException('Jumlah stok keluar minimal 1.');
        }

        $pdo->beginTransaction();
        $stmt = $pdo->prepare('SELECT * FROM barang WHERE id = ? FOR UPDATE');
        $stmt->execute([$barangId]);
        $barang = $stmt->fetch();
        if (!$barang) {
            throw new RuntimeException('Barang tidak ditemukan.');
        }

        $sebelum = (int) $barang['stok'];
        if ($jumlah > $sebelum) {
            throw new RuntimeException('Stok tidak cukup. Tersedia ' . $sebelum . '.');
        }

        $sesudah = $sebelum - $jumlah;
        $pdo->prepare('UPDATE barang SET stok = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$sesudah, $barangId]);

        $pdo->prepare(
            'INSERT INTO riwayat (barang_id, tipe, tanggal, jumlah, stok_sebelum, stok_sesudah, kode, nama, kategori, gudang, keterangan)
             VALUES (?, "OUT", ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $barangId,
            $tanggal,
            $jumlah,
            $sebelum,
            $sesudah,
            $barang['kode'],
            $barang['nama'],
            $barang['kategori'],
            $barang['gudang'],
            $keterangan === '' ? null : $keterangan,
        ]);

        $pdo->commit();
        flash('Barang keluar tercatat. Stok ' . $barang['nama'] . ' sekarang ' . $sesudah . '.');
        header('Location: riwayat.php');
        exit;
    }

    throw new RuntimeException('Aksi tidak dikenali.');
} catch (Throwable $err) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash($err->getMessage(), 'error');
    $back = $aksi === 'keluar' ? 'keluar.php' : 'masuk.php';
    header('Location: ' . $back);
    exit;
}
