CREATE DATABASE IF NOT EXISTS ecommerce_db;
USE ecommerce_db;

CREATE TABLE IF NOT EXISTS users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50)  UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role     ENUM('penjual', 'pembeli') NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    harga       INT          NOT NULL,
    stok        INT          NOT NULL,
    foto_barang VARCHAR(255) NOT NULL,
    penjual_id  INT,
    FOREIGN KEY (penjual_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Akun demo (plain password — sesuaikan jika pakai hashing)
INSERT IGNORE INTO users (username, password, role) VALUES
('Naufal',       'password123', 'penjual'),
('Ruth',         'password123', 'pembeli'),
('toko_naumi',   'password123', 'penjual'),
('buyer_mpay1',  'password123', 'pembeli');