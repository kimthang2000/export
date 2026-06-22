FROM php:8.2-fpm

# Cập nhật package list và cài đặt các thư viện cần thiết
RUN apt-get update && apt-get install -y \
    git \
    supervisor \
    libmemcached-dev \
    libz-dev \
    libpq-dev \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli mbstring zip exif gd intl opcache \
    && pecl install memcached xdebug \
    && docker-php-ext-enable memcached mysqli mbstring zip exif gd intl opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN mkdir -p /var/log/supervisor

COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
#COPY supervisord.conf /etc/supervisor/supervisord.conf


#RUN addgroup --system app && adduser --system --ingroup app appuser

#USER appuser

#CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]
