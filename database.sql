CREATE DATABASE IF NOT EXISTS stok_gudang
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE stok_gudang;

CREATE TABLE IF NOT EXISTS barang (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(50) NULL,
  nama VARCHAR(180) NOT NULL,
  kategori VARCHAR(120) NULL,
  gudang VARCHAR(150) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  keterangan TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_kode (kode),
  INDEX idx_nama (nama),
  INDEX idx_gudang (gudang),
  INDEX idx_kategori (kategori)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS riwayat (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  barang_id INT UNSIGNED NOT NULL,
  tipe ENUM('IN', 'OUT') NOT NULL,
  tanggal DATE NOT NULL,
  jumlah INT UNSIGNED NOT NULL,
  stok_sebelum INT NOT NULL DEFAULT 0,
  stok_sesudah INT NOT NULL DEFAULT 0,
  kode VARCHAR(50) NULL,
  nama VARCHAR(180) NOT NULL,
  kategori VARCHAR(120) NULL,
  gudang VARCHAR(150) NOT NULL,
  keterangan TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_riwayat_barang FOREIGN KEY (barang_id) REFERENCES barang(id) ON DELETE RESTRICT,
  INDEX idx_tipe (tipe),
  INDEX idx_tanggal (tanggal),
  INDEX idx_barang (barang_id)
) ENGINE=InnoDB;
