echo "Очистка таблиц в БД..."
path=$(dirname "$(readlink -f "$0")")
. "$path/sql-execute.sh"
sql-execute "/var/lib/postgresql/sql-scripts" "tables-reset"