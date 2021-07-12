FROM composer:2

FROM php:8-cli

COPY --from=composer /usr/bin/composer /usr/bin/composer

VOLUME /app
WORKDIR /app

RUN apt update && apt install -y \
    git \
    unzip libzip-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip

ENTRYPOINT [ "composer" ]
CMD [ "install" ]
