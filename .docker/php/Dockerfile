FROM php:8.3.4-cli-bookworm

ARG COMPOSER_VERSION=2.7.2
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer --version=$COMPOSER_VERSION

RUN apt-get update \
    && apt-get install -y curl git unzip librabbitmq-dev libyaml-dev librdkafka-dev

#https://arnaud.le-blanc.net/php-rdkafka-doc/phpdoc/rdkafka.setup.html
RUN pecl install amqp-2.1.2 yaml-2.2.3 rdkafka
RUN docker-php-ext-enable amqp yaml rdkafka
RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

#xdebug
RUN pecl install xdebug-3.3.1 \
    && docker-php-ext-enable xdebug

WORKDIR "/app"

ARG APP_USER_ID
RUN useradd -s /bin/bash -u ${APP_USER_ID} appuser
USER appuser
