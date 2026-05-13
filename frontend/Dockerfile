FROM php:8.2-apache
COPY . /var/www/html/
# Setting default index ke login.php jika user mengakses IP langsung
RUN echo "DirectoryIndex login.php" >> /etc/apache2/apache2.conf
EXPOSE 80