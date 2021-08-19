function sql-execute()
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
