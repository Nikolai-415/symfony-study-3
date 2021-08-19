docker volume rm $(docker volume ls -q)
docker rmi $(docker images -q)
docker builder prune -af