FROM alpine:3.8

ENV \
    # When using Composer, disable the warning about running commands as root/super user
    COMPOSER_ALLOW_SUPERUSER=1 \
    APP_DIR="/beyond_importer" \
    # Persistent runtime dependencies
    DEPS="php7.1 \
        php7.1-phar \
        php7.1-bcmath \
        php7.1-calendar \
        php7.1-mbstring \
        php7.1-exif \
        php7.1-ftp \
        php7.1-openssl \
        php7.1-zip \
        php7.1-sysvsem \
        php7.1-sysvshm \
        php7.1-sysvmsg \
        php7.1-shmop \
        php7.1-sockets \
        php7.1-zlib \
        php7.1-bz2 \
        php7.1-curl \
        php7.1-simplexml \
        php7.1-xml \
        php7.1-opcache \
        php7.1-dom \
        php7.1-xmlreader \
        php7.1-xmlwriter \
        php7.1-tokenizer \
        php7.1-ctype \
        php7.1-session \
        php7.1-fileinfo \
        php7.1-iconv \
        php7.1-json \
        php7.1-posix \
        curl \
        ca-certificates"

# PHP.earth Alpine repository for better developer experience
ADD https://repos.php.earth/alpine/phpearth.rsa.pub /etc/apk/keys/phpearth.rsa.pub

RUN set -x \
    && echo "https://repos.php.earth/alpine/v3.8" >> /etc/apk/repositories \
    && apk add --no-cache $DEPS

RUN curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer

COPY . $APP_DIR
WORKDIR $APP_DIR

#COPY composer.json ./
#COPY composer.lock ./
RUN composer install
CMD php artisan serve --host=0.0.0.0
EXPOSE 8000
