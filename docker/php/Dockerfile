FROM php:8.2-fpm-alpine

# システムパッケージをインストール
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \     # PNG画像処理
    libxml2-dev \    # XML処理(Laravel内部で使用)
    libzip-dev \     # ZIP解凍
    zip \
    unzip \
    oniguruma-dev

# PHP拡張をインストール
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \           # 画像のメタデータ読み取り
    pcntl \          # プロセス制御
    bcmath \         # 高精度な数値計算
    gd \             # 画像のリサイズ
    zip

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

#PHPの設定をコピー
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# 作業ディレクトリ(コンテナ内でコマンドを実行する起点)
WORKDIR /var/www/backend
