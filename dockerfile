FROM php:8.0.9-fpm-buster

# Корень проекта
ARG APP_ROOT="/var/www/html"
COPY . ${APP_ROOT}

# Исправление исключений "Unable to create..." / "Unable to write..." и т.п.
RUN chmod -R 777 /var/www/html

# Копируем настройки ini
COPY ./config/ini /usr/local/etc/php

# Генерация файла "php.ini" на основе значения APP_ENV из файла .env.local.php
RUN php -r "function getMyArr(){ if(file_exists('${APP_ROOT}/.env.local.php')){ return include '${APP_ROOT}/.env.local.php'; } else return array('PHP_INI_FILE_NAME' => 'php.ini-development'); } copy('${APP_ROOT}/config/ini/'.getMyArr()['PHP_INI_FILE_NAME'], '/usr/local/etc/php/php.ini');"

# Скачивание и включение XDebug
RUN pecl install xdebug-3.0.4 && docker-php-ext-enable xdebug

# Необходимо для скачивания расширения при выполнении команды "RUN apt install unzip"
RUN apt-get update

# Необходимо для распаковки пакетов при выполнении команды "RUN composer install"
RUN apt install unzip

# Установка Composer'а
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --version=2.1.5 --filename=composer --install-dir=/usr/local/bin
RUN php -r "unlink('composer-setup.php');"

# Проверка и установка пакетов
RUN composer install && composer dump-autoload

WORKDIR ${APP_ROOT}/public