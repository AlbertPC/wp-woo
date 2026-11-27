FROM wordpress:cli-2.12.0-php8.3 AS wpcli
FROM wordpress:7.1.4-php8.3-apache

ARG DEV_UID=1000
ARG DEV_GID=1000
RUN groupmod --gid "$DEV_GID" www-data \
    && usermod --uid "$DEV_UID" --gid "$DEV_GID" www-data \
    && chown www-data:www-data /var/www/html

COPY --from=wpcli /usr/local/bin/wp /usr/local/bin/wp
