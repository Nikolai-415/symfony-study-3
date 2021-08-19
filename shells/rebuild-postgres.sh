docker volume rm symfony-study-3_volume_postgres-data
docker rmi symfony-study-3_image_postgres:13.4-buster
docker builder prune -af
docker build ./postgres -t symfony-study-3_image_postgres:13.4-buster