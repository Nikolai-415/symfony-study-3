FROM php:8.0.9-fpm-buster
COPY ./source /var/www/html
WORKDIR /var/www/html/public

# Изменение прав папки для исправления исключения "Unable to write in ..."
RUN chmod -R 777 /var/www/html