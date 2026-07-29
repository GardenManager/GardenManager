FROM php:8.5-fpm-alpine AS base

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache \
        icu-libs \
        libpq \
        libzip \
        linux-headers \
        su-exec \
        supervisor \
    && install-php-extensions \
        pdo_pgsql \
        pgsql \
        intl \
        opcache \
        zip \
        sockets \
        redis

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app \
    && sed -i -e 's/^user = .*/user = app/' -e 's/^group = .*/group = app/' /usr/local/etc/php-fpm.d/www.conf

WORKDIR /srv/app

COPY .docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]
EXPOSE 9000

FROM node:22-alpine AS node-deps

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci

FROM base AS deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock symfony.lock ./
RUN SYMFONY_SKIP_AUTO_SCRIPTS=1 composer install --no-interaction --no-scripts --no-autoloader --prefer-dist

FROM deps AS dev

RUN install-php-extensions xdebug
COPY .docker/php/conf.d/xdebug.ini $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
COPY . .
RUN SYMFONY_SKIP_AUTO_SCRIPTS=1 composer dump-autoload --optimize

FROM base AS prod

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY .docker/php/conf.d/opcache.ini $PHP_INI_DIR/conf.d/opcache.ini
COPY --chown=app:app . .
COPY --from=deps --chown=app:app /srv/app/vendor ./vendor
COPY --from=node-deps --chown=app:app /app/node_modules ./node_modules
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative \
    && composer dump-env prod \
    && bin/console cache:clear \
    && bin/console assets:install \
    && bin/console importmap:install \
    && bin/console tailwind:build --minify \
    && bin/console asset-map:compile \
    && rm /usr/bin/composer

USER app
