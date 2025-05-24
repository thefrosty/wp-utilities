ARG PHP_VERSION=8.3
ARG WP_VERSION=6.8.1
FROM wordpress:${WP_VERSION}-php${PHP_VERSION}-fpm

COPY --from=ghcr.io/mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions mysqli pdo_mysql xdebug

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp;

RUN echo "Dockerfile has run!"
