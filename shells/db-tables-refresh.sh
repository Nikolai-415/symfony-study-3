echo "Перезаполнение таблиц в БД..."
path=$(dirname "$(readlink -f "$0")")
bash "$path/db-tables-reset.sh"
bash "$path/db-tables-seed.sh"