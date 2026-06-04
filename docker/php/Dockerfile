FROM php:8.4-fpm-alpine

# システムパッケージをインストール
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev

# PHP拡張をインストール(opcacheは拡張を入れるだけ。ON/OFFは.iniで制御)
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#PHPの設定をコピー
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# 作業ディレクトリ(コンテナ内でコマンドを実行する起点)
WORKDIR /var/www/backend
