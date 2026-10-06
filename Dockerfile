# syntax=docker/dockerfile:1.7

ARG FRANKENPHP_IMAGE=dunglas/frankenphp:1-php8.4-alpine

# ── Frontend assets ──────────────────────────────────────────────
FROM node:22-alpine AS assets
WORKDIR /app
RUN corepack enable

COPY package.json pnpm-lock.yaml .npmrc ./
RUN --mount=type=cache,target=/root/.local/share/pnpm/store \
    pnpm install --frozen-lockfile

COPY resources ./resources
COPY tsconfig.json vite.config.js ./
RUN pnpm run build

# ── PHP base (shared by dev and prod) ────────────────────────────
FROM ${FRANKENPHP_IMAGE} AS base
WORKDIR /app
RUN install-php-extensions mongodb opcache

# ── Local development ────────────────────────────────────────────
FROM base AS dev
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN cp "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
ENV SERVER_NAME=:5678

# ── Build: vendor + app + autoload ───────────────────────────────
FROM base AS build
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_CACHE_DIR=/tmp/composer-cache

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    composer install --no-dev --no-autoloader --no-scripts \
        --no-interaction --no-progress --prefer-dist

COPY . .
COPY --from=assets /app/public/build ./public/build

RUN mkdir -p bootstrap/cache storage/app/public storage/framework/cache/data \
        storage/framework/sessions storage/framework/views storage/logs \
    && composer dump-autoload --classmap-authoritative --no-dev --no-scripts \
    && rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data storage bootstrap/cache

# ── Production runtime ───────────────────────────────────────────
FROM base AS production

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    SERVER_NAME=:5678

RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf '%s\n' \
        'opcache.validate_timestamps=0' \
        'opcache.memory_consumption=256' \
        'opcache.max_accelerated_files=20000' \
        > "$PHP_INI_DIR/conf.d/zz-opcache.ini" \
    && setcap -r /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /config/caddy /data/caddy

COPY --from=build /app /app

USER www-data
EXPOSE 5678

HEALTHCHECK --interval=30s --timeout=3s --start-period=10s \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1:5678/api/health") === false ? 1 : 0);'

CMD ["sh", "-c", "php artisan migrate --force && exec frankenphp run --config /etc/frankenphp/Caddyfile"]
