<?php
declare(strict_types=1);

function riwayat_filters(): array
{
    return [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'tipe' => trim((string) ($_GET['tipe'] ?? '')),
        'gudang' => trim((string) ($_GET['gudang'] ?? '')),
        'dari' => trim((string) ($_GET['dari'] ?? '')),
        'sampai' => trim((string) ($_GET['sampai'] ?? '')),
    ];
}

function riwayat_sql(array $f): array
{
    $sql = 'SELECT * FROM riwayat WHERE 1=1';
    $params = [];

    if ($f['q'] !== '') {
        $sql .= ' AND (nama LIKE ? OR kode LIKE ? OR kategori LIKE ? OR keterangan LIKE ?)';
        $like = '%' . $f['q'] . '%';
        array_push($params, $like, $like, $like, $like);
    }
    if ($f['tipe'] === 'IN' || $f['tipe'] === 'OUT') {
        $sql .= ' AND tipe = ?';
        $params[] = $f['tipe'];
    }
    if ($f['gudang'] !== '') {
        $sql .= ' AND gudang = ?';
        $params[] = $f['gudang'];
    }
    if ($f['dari'] !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['dari'])) {
        $sql .= ' AND tanggal >= ?';
        $params[] = $f['dari'];
    }
    if ($f['sampai'] !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $f['sampai'])) {
        $sql .= ' AND tanggal <= ?';
        $params[] = $f['sampai'];
    }

    $sql .= ' ORDER BY tanggal DESC, id DESC';
    return [$sql, $params];
}
