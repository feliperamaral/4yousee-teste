FROM php:8.4-fpm-alpine

ENV TZ=America/Sao_Paulo

WORKDIR /home/www-data

RUN <<CMD
    set -eux

    echo "date.timezone = \"$TZ\"" > /usr/local/etc/php/conf.d/timezone.ini

    apk add --no-cache \
            nginx       \
            htop         \
            bash          \
            linux-headers  \
            libzip-dev      \
            libpq-dev        \
            ffmpeg

    docker-php-ext-configure opcache

    pecl install -f apcu xdebug

    docker-php-ext-install \
            -j$(nproc)      \
                opcache      \
                pdo_pgsql     \
                pcntl          \
                sockets

    docker-php-ext-enable apcu opcache

    apk del linux-headers

CMD
COPY --from=composer:2  /usr/bin/composer   /usr/bin/composer

VOLUME /root/.composer

COPY composer* .

RUN <<CMD
    composer install --download-only --no-interaction --no-plugins  --no-scripts
CMD

COPY ./linux /



STOPSIGNAL SIGKILL

CMD ["bash", "/start/api.sh"]
