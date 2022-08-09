# Образ PHP
ARG PHP_VERSION="8.1.9"
FROM php:${PHP_VERSION}-fpm-bullseye

# Корень проекта
ARG APP_ROOT="/var/www/html"
# Версия Xdebug
ARG XDEBUG_VERSION="3.1.5"
# Версия Composer
ARG COMPOSER_VERSION="2.3.10"

WORKDIR ${APP_ROOT}
COPY . ${APP_ROOT}

# Исправление исключений "Unable to create..." / "Unable to write..." и т.п.
RUN chmod -R 777 ${APP_ROOT}

# Копируем настройки ini
COPY ./config/ini /usr/local/etc/php

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

# Установка Composer'а
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --version=${COMPOSER_VERSION} --filename=composer --install-dir=/usr/local/bin
RUN php -r "unlink('composer-setup.php');"

# Необходимо для распаковки пакетов при выполнении команды "RUN composer install"
RUN apt install unzip

# Проверка и установка пакетов
RUN composer update
RUN composer install
RUN composer dump-autoload

WORKDIR ${APP_ROOT}/public
