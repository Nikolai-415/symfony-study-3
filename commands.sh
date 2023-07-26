#!/bin/bash

function n2038_sql_execute()
{
    path=$1
    folder_name=$2
    
    echo "Выполнение SQL файлов из директории $path/$folder_name/..."

    for file_name in $(docker exec working_with_resumes.container.postgres sh -c "find $path/$folder_name/*.sql -maxdepth 1 -printf \"%f\n\""); do
        command='psql -U ${POSTGRES_USER} -d ${POSTGRES_DB} -f '"$path/$folder_name/$file_name"
        echo "Выполнение файла "$file_name"..."
        docker exec working_with_resumes.container.postgres sh -c "$command"
    done
}

function n2038_execute_command()
{
    command=$1
    shift
    
    if [ "$command" = "help" ]; then
        echo "============================================================================================"
        echo "| Команда                     | Описание"
        echo "============================================================================================"
        echo "| help                        | Вывести список всех команд"
        echo "| build php                   | Собрать PHP"
        echo "| build postgres              | Собрать PostgreSQL"
        echo "| build project               | Собрать весь проект"
        echo "| rebuild php                 | Пересобрать PHP"
        echo "| rebuild postgres            | Пересобрать PostgreSQL"
        echo "| rebuild project             | Пересобрать весь проект"
        echo "| tables create               | Создать таблицы в БД"
        echo "| tables seed                 | Заполнить таблицы в БД"
        echo "| tables drop                 | Удалить таблицы в БД"
        echo "| tables refresh              | Пересоздать и заполнить таблицы в БД"
        echo "| up                          | Запустить проект"
        echo "| down                        | Остановить проект"
        echo "| restart                     | Перезапустить проект"
        echo "| clear php                   | Очистить PHP"
        echo "| clear postgres              | Очистить PostgreSQL"
        echo "| clear project               | Очистить весь проект"
        echo "| set working dev             | Сменить окружение на Development во время работы контейнеров"
        echo "| set stopped dev             | Сменить окружение на Development если контейнеры остановлены"
        echo "| set working prod            | Сменить окружение на Production во время работы контейнеров"
        echo "| set stopped prod            | Сменить окружение на Production если контейнеры остановлены"
        echo "============================================================================================"
        return 0
    elif [ "$command" = "build" ]; then
        if [ "$1" = "php" ]; then
            echo "Сборка PHP..."
            n2038_execute_command set stopped $n2038_app_env
            docker-compose build php || return 1
            echo "PHP собран!"
            return 0
        elif [ "$1" = "postgres" ]; then
            echo "Сборка Postgres..."
            docker-compose build postgres || return 1
            echo "Postgres собран!"
            return 0
        elif [ "$1" = "project" ]; then
            echo "Сборка проекта..."
            docker-compose build || return 1
            docker pull nginx:1.23.1 || return 1
            docker pull dpage/pgadmin4:6.12 || return 1
            echo "Проект собран!"
            return 0
        else
            echo "Неверный аргумент! Допустимые значения: \"php\", \"postgres\", \"project\"."
        fi
    elif [ "$command" = "rebuild" ]; then
        if [ "$1" = "php" ]; then
            echo "Пересборка PHP..."
            n2038_execute_command clear php || return 1
            n2038_execute_command build php || return 1
            echo "PHP пересобран!"
            return 0
        elif [ "$1" = "postgres" ]; then
            echo "Пересборка Postgres..."
            n2038_execute_command clear postgres || return 1
            n2038_execute_command build postgres || return 1
            echo "Postgres пересобран!"
            return 0
        elif [ "$1" = "project" ]; then
            echo "Пересборка проекта..."
            n2038_execute_command clear project || return 1
            n2038_execute_command build project || return 1
            echo "Проект пересобран!"
            return 0
        else
            echo "Неверный аргумент! Допустимые значения: \"php\", \"postgres\", \"project\"."
        fi
    elif [ "$command" = "up" ]; then
            echo "Запуск системы..."
            docker-compose up -d || return 1
            docker exec working_with_resumes.container.php sh -c "cd .. && composer dump-env $n2038_app_env && php bin/console cache:clear"
            echo "Система запущена! Адрес: http:\\\\localhost\\"
            return 0
    elif [ "$command" = "down" ]; then
            echo "Остановка системы..."
            docker-compose down || return 1
            echo "Система остановлена!"
            return 0
    elif [ "$command" = "restart" ]; then
            echo "Перезапуск системы..."
            n2038_execute_command down || return 1
            n2038_execute_command up || return 1
            echo "Система перезапущена!"
            return 0
    elif [ "$command" = "clear" ]; then
        if [ "$1" = "php" ]; then
            echo "Очистка PHP..."
            docker volume rm working_with_resumes.volume.php-var
            docker volume rm working_with_resumes.volume.php-vendor
            docker rmi working_with_resumes/image/php:1.0.0
            docker builder prune -af
            echo "PHP очищен!"
            return 0
        elif [ "$1" = "postgres" ]; then
            echo "Очистка Postgres..."
            [ -d ./postgres/data ] && rm -r ./postgres/data
            [ -d ./pgadmin/data ] && rm -r ./pgadmin/data
            docker rmi working_with_resumes/image/postgres:1.0.0
            docker builder prune -af
            echo "Postgres очищен!"
            return 0
        elif [ "$1" = "project" ]; then
            echo "Очистка проекта..."
            n2038_execute_command clear php
            n2038_execute_command clear postgres
            echo "Проект очищен!"
            return 0
        elif [ "$1" = "all" ]; then
            docker volume rm $(docker volume ls -q)
            docker rmi $(docker images -q)
            docker builder prune -af
            return 0
        else
            echo "Неверный аргумент! Допустимые значения: \"php\", \"postgres\", \"project\"."
        fi
    elif [ "$command" = "tables" ]; then
        if [ "$1" = "create" ]; then
            echo "Создание таблиц в БД..."
            n2038_sql_execute "/var/lib/postgresql/sql-scripts" "tables-create"
            echo "Таблицы созданы!"
            return 0
        elif [ "$1" = "seed" ]; then
            echo "Заполнение таблиц в БД..."
            n2038_sql_execute "/var/lib/postgresql/sql-scripts" "tables-seed"
            echo "Таблицы заполнены!"
            return 0
        elif [ "$1" = "drop" ]; then
            echo "Удаление таблиц из БД..."
            n2038_sql_execute "/var/lib/postgresql/sql-scripts" "tables-drop"
            echo "Таблицы удалены!"
            return 0
        elif [ "$1" = "refresh" ]; then
            echo "Перезаполнение таблиц в БД..."
            n2038_execute_command tables drop
            n2038_execute_command tables create
            n2038_execute_command tables seed
            echo "Таблицы перезаполнены!"
            return 0
        else
            echo "Неверный аргумент! Допустимые значения: \"create\", \"seed\", \"drop\", \"refresh\"."
        fi
    elif [ "$command" = "set" ]; then
        if [ "$1" = "working" ]; then
            if [ "$2" = "dev" ]; then
                n2038_app_env="dev"
                cp "./config/ini/php.ini-development" "./config/ini/php.ini"
                docker exec working_with_resumes.container.php sh -c "cd .. && composer dump-env $n2038_app_env && php bin/console cache:clear"
                echo "Установлено окружение Development! Необходимо перезапустить проект. Команда: n2038 restart."
                return 0
            elif [ "$2" = "prod" ]; then
                n2038_app_env="prod"
                cp "./config/ini/php.ini-production" "./config/ini/php.ini"
                docker exec working_with_resumes.container.php sh -c "cd .. && composer dump-env $n2038_app_env && php bin/console cache:clear"
                echo "Установлено окружение Production! Необходимо перезапустить проект. Команда: n2038 restart."
                return 0
            else
                echo "Неверный аргумент 2! Допустимые значения: \"dev\", \"prod\"."
            fi
        elif [ "$1" = "stopped" ]; then
            if [ "$2" = "dev" ]; then
                n2038_app_env="dev"
                cp "./config/ini/php.ini-development" "./config/ini/php.ini"
                echo "Установлено окружение Development!"
                return 0
            elif [ "$2" = "prod" ]; then
                n2038_app_env="prod"
                cp "./config/ini/php.ini-production" "./config/ini/php.ini"
                echo "Установлено окружение Production!"
                return 0
            else
                echo "Неверный аргумент 2! Допустимые значения: \"dev\", \"prod\"."
            fi
        else
            echo "Неверный аргумент 1! Допустимые значения: \"working\", \"stopped\"."
        fi
    else
        echo "Неизвестная команда!"
        n2038 help
    fi
    return 1
}

function n2038
{
    echo "Выполнение команды \"n2038 $@\"..."
    n2038_execute_command $@
    return_code=$?
    if [ "$return_code" -eq "1" ]; then
        echo "Команда \"n2038 $@\" прервана."
    fi
}

echo "Команды добавлены!"
n2038 help