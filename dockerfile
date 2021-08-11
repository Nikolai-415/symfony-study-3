FROM php:8.0.9-fpm-buster

COPY ./config/ini/php.ini /usr/local/etc/php/conf.d/php.ini

COPY . /var/www/html

# Необходимо для скачивания расширения при выполнении команды "RUN apt install unzip"
RUN apt-get update

# Необходимо для распаковки пакетов при выполнении команды "RUN composer install"
RUN apt install unzip

# Установка Composer'а
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === '756890a4488ce9024fc62c56153228907f1545c228516cbf63f885e036d37e9a59d27d63f46af1d4d07ee0f76181c7d3') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php --version=2.1.0 --filename=composer --install-dir=/usr/local/bin
RUN php -r "unlink('composer-setup.php');"

# Проверка и установка пакетов
RUN composer install && composer dump-autoload

WORKDIR /var/www/html/public