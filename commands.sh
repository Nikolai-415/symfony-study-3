function n415_sql_execute()
{
    path=$1
    folder_name=$2
    
    echo "Выполнение SQL файлов из директории $path/$folder_name/..."

    for file_name in $(docker exec symfony-study-3_container_postgres sh -c "find $path/$folder_name/*.sql -maxdepth 1 -printf \"%f\n\""); do
        command='psql -U ${POSTGRES_USER} -d ${POSTGRES_DB} -f '"$path/$folder_name/$file_name"
        echo "Выполнение файла "$file_name"..."
        docker exec symfony-study-3_container_postgres sh -c "$command"
    done
}

function n415_execute_command()
{
    command=$1
    shift
    
    if [ "$command" = "help" ]; then
        echo "==========================================================================="
        echo "| Команда                     | Описание"
        echo "==========================================================================="
        echo "| help                        | Вывести список всех команд"
        echo "| build php                   | Собрать/пересобрать PHP"
        echo "| build postgres              | Собрать/пересобрать PostgreSQL"
        echo "| build project               | Собрать/пересобрать весь проект"
        echo "| db tables create            | Создать таблицы в БД"
        echo "| db tables seed              | Заполнить таблицы в БД"
        echo "| db tables drop              | Удалить таблицы в БД"
        echo "| db tables refresh           | Пересоздать и заполнить таблицы в БД"
        echo "| up                          | Запустить проект"
        echo "| down                        | Остановить проект"
        echo "| clear php                   | Очистить PHP"
        echo "| clear postgres              | Очистить PostgreSQL"
        echo "| clear project               | Очистить весь проект"
        echo "==========================================================================="
        return 0
    elif [ "$command" = "build" ]; then
        if [ "$1" = "php" ]; then
            n415_execute_command clear php
            docker build . -t symfony-study-3_image_php:8.0.9-fpm-buster
            return 0
        elif [ "$1" = "postgres" ]; then
            n415_execute_command clear postgres
            docker build ./postgres -t symfony-study-3_image_postgres:13.4-buster
            return 0
        elif [ "$1" = "project" ]; then
            n415_execute_command clear project
            docker-compose build
            docker pull nginx:1.21.1
            docker pull dpage/pgadmin4:5.6
            return 0
        else
            echo "Неверный аргумент 1! Допустимые значения: \"php\", \"postgres\", \"project\"."
        fi
    elif [ "$command" = "up" ]; then
            docker-compose up -d
            return 0
    elif [ "$command" = "down" ]; then
            docker-compose down
            return 0
    elif [ "$command" = "clear" ]; then
        if [ "$1" = "php" ]; then
            docker volume rm symfony-study-3_volume_php-var
            docker volume rm symfony-study-3_volume_php-vendor
            docker rmi symfony-study-3_image_php:8.0.9-fpm-buster
            docker builder prune -af
            return 0
        elif [ "$1" = "postgres" ]; then
            docker volume rm symfony-study-3_volume_postgres-data
            docker rmi symfony-study-3_image_postgres:13.4-buster
            docker builder prune -af
            return 0
        elif [ "$1" = "project" ]; then
            n415_execute_command clear php
            n415_execute_command clear postgres
            return 0
        elif [ "$1" = "all" ]; then
            docker volume rm $(docker volume ls -q)
            docker rmi $(docker images -q)
            docker builder prune -af
            return 0
        else
            echo "Неверный аргумент 1! Допустимые значения: \"php\", \"postgres\", \"project\"."
        fi
    elif [ "$command" = "db" ]; then
        if [ "$1" = "tables" ]; then
            if [ "$2" = "create" ]; then
                echo "Создание таблиц в БД..."
                n415_sql_execute "/var/lib/postgresql/sql-scripts" "tables-create"
                return 0
            elif [ "$2" = "seed" ]; then
                echo "Заполнение таблиц в БД..."
                n415_sql_execute "/var/lib/postgresql/sql-scripts" "tables-seed"
                return 0
            elif [ "$2" = "drop" ]; then
                echo "Удаление таблиц из БД..."
                n415_sql_execute "/var/lib/postgresql/sql-scripts" "tables-drop"
                return 0
            elif [ "$2" = "refresh" ]; then
                echo "Перезаполнение таблиц в БД..."
                n415_execute_command db tables drop
                n415_execute_command db tables create
                n415_execute_command db tables seed
                return 0
            else
                echo "Неверный аргумент 2! Допустимые значения: \"create\", \"seed\", \"drop\", \"refresh\"."
            fi
        else
            echo "Неверный аргумент 1! Допустимые значения: \"tables\"."
        fi
    else
        echo "Неизвестная команда!"
        n415 help
    fi
    return 1
}

function n415
{
    echo "Выполнение команды \"n415 $@\"..."
    n415_execute_command $@
    return_code=$?
    if [ "$return_code" -eq "1" ]; then
        echo "Команда \"n415 $@\" прервана."
    fi
}

echo "Команды добавлены!"
n415 help