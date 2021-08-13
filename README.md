<h1>symfony-study-3</h1>

Тестовый проект для изучения PHP-фреймворка Symfony

<h2>Используемые технологии</h2>

<ol>
  <li><i><b>Docker-Compose v.3.9</b></i>;</li>
  <li><i><b>PHP v.8.0.9</b></i>;</li>
  <li><i><b>Symfony v.5.3.6</b></i>;</li>
  <li><i><b>Xdebug v.3.0.4</b></i>;</li>
  <li><i><b>Composer v.2.1.5</b></i>;</li>
  <li><i><b>Nginx v.1.21.1</b></i>.</li>
</ol>

<h2>Необходимые компоненты</h2>

<ol>
  <li>Docker (используется Docker-Compose);</li>
  <li>Composer (не обязательно - будет использоваться Composer из контейнера).</li>
</ol>

<h2>Установка (при наличии Composer'а)</h2>

<ol>
  <li>Склонировать репозиторий;</li>
  <li>Запустить терминал в корне проекта;</li>
  <li>Сгенерировать файл env-переменных окружения командой:<br/>
  <code>composer dump-env dev</code> для development<br/>
  <code>composer dump-env prod</code> для production</li>
  <li>Собрать конфигурацию Docker-Compose командой:<br/>
  <code>docker-compose build</code></li>
</ol>

<h2>Установка (при отсутствии Composer'а)</h2>

<ol>
  <li>Склонировать репозиторий;</li>
  <li>Запустить терминал в корне проекта;</li>
  <li>Собрать и запустить образ PHP в конфигурации Docker-Compose командой:<br/>
  <code>docker-compose up -d --build php</code><br/>
  Дождаться запуска контейнера;</li>
  <li>Зайти в консоль контейнера PHP (имеет название <i><b>symfony-study-3_container-php</b></i>). На Windows это команда:<br/>
  <code>winpty docker exec -it symfony-study-3_container-php //bin//sh</code></li>
  <li>Перейти на уровень вверх командой:<br/>
  <code>cd ..</code></li>
  <li>Сгенерировать файл env-переменных окружения командой:<br/>
  <code>composer dump-env dev</code> для development<br/>
  <code>composer dump-env prod</code> для production</li>
  <li>Выйти из контейнера командой:<br/>
  <code>exit</code></li>
  <li>Остановить конфигурацию Docker-Compose командой:<br/>
  <code>docker-compose down</code></li>
  <li>Удалить старый образ PHP командой:<br/>
  <code>docker rmi symfony-study-3_image-php</code></li>
  <li>Собрать конфигурацию Docker-Compose командой:<br/>
  <code>docker-compose build</code></li>
</ol>

<h2>Запуск</h2>

<ol>
  <li>Запустить конфигурацию Docker-Compose командой:<br/>
  <code>docker-compose up -d</code><br/>
  Дождаться запуска всех контейнеров;</li>
  <li>Система будет доступна по адресу:<br/>
  <a href="http://localhost:80/" target="_blank">http://localhost:80/</a></li>
</ol>

<h2>Остановка</h2>

<ol>
  <li>Остановить конфигурацию Docker-Compose командой:<br/>
  <code>docker-compose down</code></li>
</ol>

<h2>Смена окружения (при наличии Composer'а)</h2>

<ol>
  <li>(Если конфигурация Docker-Compose запущена) Остановить конфигурацию Docker-Compose командой:<br/>
  <code>docker-compose down</code></li>
  <li>Удалить старый образ PHP командой:<br/>
  <code>docker rmi symfony-study-3_image-php</code></li>
  <li>Выполнить пункты 3-4 из <a href="#установка-при-наличии-composerа">установки (при наличии Composer'а)</a>.</li>
</ol>

<h2>Смена окружения (при отсутствии Composer'а)</h2>

<ol>
  <li>(Если конфигурация Docker-Compose остановлена) Запустить образ PHP в конфигурации Docker-Compose командой:<br/>
  <code>docker-compose up -d php</code><br/>
  Дождаться запуска контейнера;</li>
  <li>Выполнить пункты 4-10 из <a href="#установка-при-отсутствии-composerа">установки (при отсутствии Composer'а)</a>.</li>
</ol>

<h2>Удаление</h2>

<ol>
  <li>Удалить сгенерированные volume'ы командами:<br/>
  <code>volume rm symfony-study-3_volume-php-var</code><br/>
  <code>volume rm symfony-study-3_volume-php-vendor</code></li>
  <li>Удалить сгенерированный образ для контейнера PHP:<br/>
  <code>docker rmi symfony-study-3_container-php</code></li>
  <li>Конфигурация проекта также использует образ <i><b>nginx:1.21.1</b></i>. Если он больше нигде не используется, то и его удалить командой:<br/>
  <code>docker rmi nginx:1.21.1</code></li>
</ol>
