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
    stok        INT          NOT NULL DEFAULT 0,
    foto_barang VARCHAR(255) NOT NULL DEFAULT '',
    kategori    VARCHAR(50)  NOT NULL DEFAULT 'Lainnya',
    deskripsi   TEXT,
    penjual_id  INT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (penjual_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT IGNORE INTO users (username, password, role) VALUES
('Naufal',      'password123', 'penjual'),
('Ruth',        'password123', 'pembeli'),
('toko_naumi',  'password123', 'penjual'),
('buyer_mpay1', 'password123', 'pembeli');

INSERT IGNORE INTO products (nama_barang, harga, stok, foto_barang, kategori, penjual_id) VALUES
('Sepatu Sneakers Putih',  250000, 45, '', 'Fashion',    1),
('Kemeja Casual Pria',     150000, 30, '', 'Fashion',    1),
('Earphone Bluetooth',     180000, 20, '', 'Elektronik', 1),
('Power Bank 10000mAh',    220000, 15, '', 'Elektronik', 1),
('Tas Ransel Laptop',      320000, 25, '', 'Fashion',    1),
('Minyak Goreng 2L',        32000, 80, '', 'Makanan',    1),
('Sabun Mandi Cair',        45000, 60, '', 'Kecantikan', 1),
('Dumbbell 5kg',           120000,  8, '', 'Olahraga',   1);