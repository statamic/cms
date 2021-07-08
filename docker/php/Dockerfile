FROM php:8.0.8-cli

VOLUME /app
WORKDIR /app

RUN apt update && apt install -y \
    tini \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# https://gist.github.com/chronon/95911d21928cff786e306c23e7d1d3f3
RUN docker-php-ext-install \
    bcmath \
    mbstring \
    pdo_mysql \
    # Enable exif (required for Statamic's Asset Upload)
    exif

# Install GD (required for Statamic's Glide)
RUN apt update && apt install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# XDebug
ARG XDEBUG='0'
RUN if [ $XDEBUG -eq 1 ]; then pecl install xdebug && docker-php-ext-enable xdebug; fi
COPY xdebug.ini /usr/local/etc/php/conf.d
COPY error_reporting.ini /usr/local/etc/php/conf.d

COPY memory_limit.ini /usr/local/etc/php/conf.d

EXPOSE 8080
ENTRYPOINT [ "tini", "--", "php" ]
CMD [ "artisan", "serve", "--port", "8080", "--host", "0.0.0.0" ]
