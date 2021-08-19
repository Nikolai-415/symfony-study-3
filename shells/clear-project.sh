docker volume rm symfony-study-3_volume_php-var
docker volume rm symfony-study-3_volume_php-vendor
docker volume rm symfony-study-3_volume_postgres-data
docker volume rm symfony-study-3_volume_pgadmin-data
docker rmi symfony-study-3_image_php:8.0.9-fpm-buster
docker rmi symfony-study-3_image_postgres:13.4-buster
docker builder prune -af