CREATE DATABASE IF NOT EXISTS flashsale_db;
USE flashsale_db;

CREATE TABLE IF NOT EXISTS barang (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    stok INT NOT NULL,
    harga INT NOT NULL
);

INSERT INTO barang (nama_barang, stok, harga) VALUES ('Sepatu Kets Awam', 100, 99000);