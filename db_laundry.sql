-- ============================================
-- MyLaundry Database Schema
-- Engine: InnoDB (untuk support Foreign Key)
-- ============================================

CREATE DATABASE IF NOT EXISTS db_laundry
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE db_laundry;

-- ============================================
-- 1. Tabel Outlet
-- ============================================
CREATE TABLE IF NOT EXISTS tb_outlet (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    telp VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 2. Tabel User
-- ============================================
CREATE TABLE IF NOT EXISTS tb_user (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_outlet INT(11) NOT NULL,
    role ENUM('admin', 'kasir', 'owner') NOT NULL DEFAULT 'kasir',
    PRIMARY KEY (id),
    FOREIGN KEY (id_outlet) REFERENCES tb_outlet(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 3. Tabel Member
-- ============================================
CREATE TABLE IF NOT EXISTS tb_member (
    id INT(11) NOT NULL AUTO_INCREMENT,
    kode_member VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    telp VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 4. Tabel Paket Layanan
-- ============================================
CREATE TABLE IF NOT EXISTS tb_paket (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama_paket VARCHAR(100) NOT NULL,
    harga_per_kg INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- 5. Tabel Transaksi
-- ============================================
CREATE TABLE IF NOT EXISTS tb_transaksi (
    id INT(11) NOT NULL AUTO_INCREMENT,
    id_outlet INT(11) NOT NULL,
    kode_invoice VARCHAR(30) NOT NULL UNIQUE,
    id_member INT(11) NOT NULL,
    id_paket INT(11) NOT NULL,
    berat DECIMAL(10,2) NOT NULL DEFAULT 0,
    subtotal INT(11) NOT NULL DEFAULT 0,
    pajak INT(11) NOT NULL DEFAULT 4000,
    biaya INT(11) NOT NULL DEFAULT 0,
    uang_muka INT(11) NOT NULL DEFAULT 0,
    kembalian INT(11) NOT NULL DEFAULT 0,
    tgl DATE NOT NULL,
    batas_waktu DATE NOT NULL,
    tgl_bayar DATE DEFAULT NULL,
    status ENUM('baru', 'proses', 'selesai', 'diambil') NOT NULL DEFAULT 'baru',
    pembayaran ENUM('dibayar', 'belum_dibayar') NOT NULL DEFAULT 'belum_dibayar',
    keterangan TEXT DEFAULT NULL,
    id_user INT(11) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (id_outlet) REFERENCES tb_outlet(id) ON DELETE CASCADE,
    FOREIGN KEY (id_member) REFERENCES tb_member(id) ON DELETE CASCADE,
    FOREIGN KEY (id_paket) REFERENCES tb_paket(id) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES tb_user(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- Data Default
-- ============================================

-- Outlet default
INSERT INTO tb_outlet (nama, alamat, telp) VALUES
('MyLaundry Pusat', 'Jl. Merdeka No. 1, Jakarta', '081234567890'),
('MyLaundry Cabang 1', 'Jl. Sudirman No. 25, Bandung', '081298765432');

-- Paket layanan
INSERT INTO tb_paket (nama_paket, harga_per_kg) VALUES
('Baju', 10000),
('Celana', 12000),
('Karpet / Selimut', 15000);

-- User default
-- admin/admin123, ahmad/ahmad123, siti/siti123, budi/budi123
INSERT INTO tb_user (nama, username, password, id_outlet, role) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 'admin'),
('Ahmad Fauzi', 'ahmad', '$2y$10$IRNJyCVaNZM57wkXoS68L.MHHzr4W1MgWkoRzUf1TirPSW.4g/34.', 1, 'owner'),
('Siti Nurhaliza', 'siti', '$2y$10$4mQhLXhia/MgiBdwXZQ3JeW.GdW5IQrJTFN/rjXfPUn8mcI.7NP8a', 1, 'kasir'),
('Budi Santoso', 'budi', '$2y$10$Gc7pve0gfkAA3BiBltz1GOGgMjFnU0wIMrSV2gW7momfxsngupkEy', 2, 'kasir');

-- Member sample
INSERT INTO tb_member (kode_member, nama, alamat, jenis_kelamin, telp) VALUES
('MBR-001', 'Ahmad Fauzi', 'Jl. Kenanga No. 10', 'L', '081300000001'),
('MBR-002', 'Siti Nurhaliza', 'Jl. Mawar No. 5', 'P', '081300000002'),
('MBR-003', 'Budi Santoso', 'Jl. Melati No. 3', 'L', '081300000003');
