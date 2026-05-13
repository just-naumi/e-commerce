# Menggunakan image resmi PHP dengan web server Apache
FROM php:8.2-apache

# Menginstal ekstensi mysqli agar PHP bisa ngobrol dengan MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Menyalin file web kita ke dalam folder server Apache di container
COPY . /var/www/html/

# Membuka port 80
EXPOSE 80