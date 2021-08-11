FROM php:8.0.9-apache-buster
COPY ./source /var/www/html
WORKDIR /var/www/html/public

# Изменение корневой папки для Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Изменение прав папки для исправления исключения "Unable to write in ..."
RUN chmod -R 777 /var/www/html