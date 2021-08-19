path=$(dirname "$(readlink -f "$0")")

bash "$path/clear-project.sh"
docker-compose build
docker pull nginx:1.21.1
docker pull dpage/pgadmin4:5.6