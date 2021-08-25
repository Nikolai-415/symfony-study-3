FROM postgres:13.4-buster
COPY ./sql-scripts /var/lib/postgresql/sql-scripts
CMD ["docker-entrypoint.sh", "postgres"]