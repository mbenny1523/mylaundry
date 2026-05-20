CREATE DATABASE IF NOT EXISTS db_laundry
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;

USE db_laundry;

CREATE TABLE IF NOT EXISTS tb_outlet (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    telp VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS tb_member (
    id INT(11) NOT NULL AUTO_INCREMENT,
    kode_member VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT NOT NULL,
    jenis_kelamin ENUM('L', 'P') NOT NULL,
    telp VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tb_paket (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nama_paket VARCHAR(100) NOT NULL,
    harga_per_kg INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

INSERT INTO tb_outlet (nama, alamat, telp) VALUES
('MyLaundry Pusat', 'Jl. Merdeka No. 1, Jakarta', '081234567890'),
('MyLaundry Cabang 1', 'Jl. Sudirman No. 25, Bandung', '081298765432');

INSERT INTO tb_paket (nama_paket, harga_per_kg) VALUES
('Baju', 10000),
('Celana', 12000),
('Karpet / Selimut', 15000);
