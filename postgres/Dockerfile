FROM postgres:14.4-bullseye
COPY ./sql-scripts /var/lib/postgresql/sql-scripts
CMD ["docker-entrypoint.sh", "postgres"]