# Образ PHP
ARG PHP_VERSION="8.1.21"
FROM php:${PHP_VERSION}-fpm-bookworm

# Корень проекта
ARG APP_ROOT="/var/www/html"

WORKDIR ${APP_ROOT}
COPY . ${APP_ROOT}

# Исправление исключений "Unable to create..." / "Unable to write..." и т.п.
RUN chmod -R 777 ${APP_ROOT}

# Копируем настройки ini
COPY ./config/ini /usr/local/etc/php

# Версия Xdebug
ARG XDEBUG_VERSION="3.2.2"

# Скачивание и включение XDebug
RUN pecl install "xdebug-${XDEBUG_VERSION}"

# Необходимо для скачиваний расширений
RUN apt-get update

# Необходимо для установки расширений для PostgreSQL
RUN apt-get install -y libpq-dev

# Установка необходимых расширений для PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Установка intl
RUN apt-get install -y libicu-dev
RUN docker-php-ext-configure intl
RUN docker-php-ext-install intl

# Версия Composer
ARG COMPOSER_VERSION="2.5.8"

# Установка Composer'а
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'e21205b207c3ff031906575712edab6f13eb0b361f2085f1f1237b7126d785e826a450292b6cfd1d64d92e6563bbde02') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --version=${COMPOSER_VERSION} --filename=composer --install-dir=/usr/local/bin
RUN php -r "unlink('composer-setup.php');"

# Необходимо для распаковки пакетов при выполнении команды "RUN composer install"
RUN apt install unzip

# Проверка и установка пакетов
RUN composer update
RUN composer install
RUN composer dump-autoload

WORKDIR ${APP_ROOT}/public
