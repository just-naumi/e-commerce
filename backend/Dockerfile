FROM php:8.2-apache
RUN docker-php-ext-install mysqli
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads
COPY . /var/www/html/
EXPOSE 80