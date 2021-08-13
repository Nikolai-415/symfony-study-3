<h1>symfony-study-3</h1>

Тестовый проект для изучения PHP-фреймворка Symfony

<h2>Используемые технологии</h2>

1. <i><b>Docker-Compose v.3.9</b></i>;
2. <i><b>PHP v.8.0.9</b></i>;
3. <i><b>Symfony v.5.3.6</b></i>;
4. <i><b>Xdebug v.3.0.4</b></i>;
5. <i><b>Composer v.2.1.5</b></i>;
6. <i><b>Nginx v.1.21.1</b></i>.

<h2>Необходимые компоненты</h2>

1. Docker (используется Docker-Compose);
2. Composer (не обязательно - будет использоваться Composer из контейнера).

<h2>Установка (при наличии Composer'а)</h2>

1. Склонировать репозиторий;
2. Запустить терминал в корне проекта;
3. Установить все пакеты Composer'а командой:<br/>
<code>composer install && composer dump-autoload</code>
4. Сгенерировать файл env-переменных окружения командой:<br/>
<code>composer dump-env dev</code> для development<br/>
<code>composer dump-env prod</code> для production
5. Собрать конфигурацию Docker-Compose командой:<br/>
<code>docker-compose build</code>

<h2>Установка (при отсутствии Composer'а)</h2>

1. Склонировать репозиторий;
2. Запустить терминал в корне проекта;
3. Собрать и запустить образ PHP в конфигурации Docker-Compose командой:<br/>
<code>docker-compose up -d --build php</code><br/>
Дождаться запуска контейнера;
4. Зайти в консоль контейнера PHP (имеет название <i><b>symfony-study-3_container-php</b></i>). На Windows это команда:<br/>
<code>winpty docker exec -it symfony-study-3_container-php //bin//sh</code>
5. Перейти на уровень вверх командой:<br/>
<code>cd ..</code>
6. Сгенерировать файл env-переменных окружения командой:<br/>
<code>composer dump-env dev</code> для development<br/>
<code>composer dump-env prod</code> для production
7. Выйти из контейнера командой:<br/>
<code>exit</code>
8. Остановить конфигурацию Docker-Compose командой:<br/>
<code>docker-compose down</code>
9. Удалить старый образ PHP командой:<br/>
<code>docker rmi symfony-study-3_image-php</code>
9. Собрать заново конфигурацию Docker-Compose командой:<br/>
<code>docker-compose build</code>

<h2>Запуск</h2>

1. Запустить конфигурацию Docker-Compose командой:<br/>
<code>docker-compose up -d</code><br/>
Дождаться запуска всех контейнеров;
2. Система будет доступна по адресу:<br/>
http://localhost:80/

<h2>Остановка</h2>

1. Остановить конфигурацию Docker-Compose командой:<br/>
<code>docker-compose down</code>

<h2>Смена окружения (при наличии Composer'а)</h2>

1. (Если конфигурация Docker-Compose запущена) Остановить конфигурацию Docker-Compose командой:<br/>
<code>docker-compose down</code>
2. Удалить старый образ PHP командой:<br/>
<code>docker rmi symfony-study-3_image-php</code>
3. Выполнить пункты 4-5 из <a href="#установка-при-наличии-composerа">установки (при наличии Composer'а)</a>.

<h2>Смена окружения (при отсутствии Composer'а)</h2>

1. (Если конфигурация Docker-Compose остановлена) Запустить образ PHP в конфигурации Docker-Compose командой:<br/>
<code>docker-compose up -d php</code><br/>
Дождаться запуска контейнера;
2. Выполнить пункты 4-11 из <a href="#установка-при-отсутствии-composerа">установки (при отсутствии Composer'а)</a>.

<h2>Удаление</h2>

1. Удалить сгенерированные volume'ы командами:<br/>
<code>volume rm symfony-study-3_volume-php-var</code><br/>
<code>volume rm symfony-study-3_volume-php-vendor</code>
2. Удалить сгенерированный образ для контейнера PHP:<br/>
<code>docker rmi symfony-study-3_container-php</code>
3. Конфигурация проекта также использует образ <i><b>nginx:1.21.1</b></i>. Если он больше нигде не используется, то и его удалить командой:<br/>
<code>docker rmi nginx:1.21.1</code>
