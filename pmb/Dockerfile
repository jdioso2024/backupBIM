FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libzip-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libicu-dev \
    libfreetype6-dev \
    libgmp-dev \
    --no-install-recommends \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        gd \
        zip \
        xml \
        mbstring \
        curl \
        intl \
        exif \
        opcache \
        pcntl \
        bcmath \
        gmp \
    && a2enmod rewrite headers expires deflate \
    && rm -rf /var/lib/apt/lists/*

RUN echo "upload_max_filesize = 64M\n\
post_max_size = 64M\n\
memory_limit = 256M\n\
max_execution_time = 300\n\
" > /usr/local/etc/php/conf.d/laravel.ini

# DocumentRoot ke public/ Laravel, seluruhnya di-mount dari NFS
RUN rm -rf /var/www/html && mkdir -p /var/www/html

# Apache DocumentRoot ke /var/www/html/public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf
