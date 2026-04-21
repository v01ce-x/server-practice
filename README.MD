# Проект web-base

Окружение использует или содержит следующие компоненты:
* [Docker](https://www.docker.com/) - контейнеризация приложений
* [Docker Compose](https://docs.docker.com/compose/) - управление контейнерами
* [Apache + PHP](https://hub.docker.com/layers/library/php/8-apache/images/sha256-1cf5e82a09672ce57b70d55ba400402099142ccbc762491129ed8ecc246c55f4?context=explore) - контейнер с веб-сервером Apache и php
* [Mariadb 10.7](https://hub.docker.com/r/168447636/mariadb) - контейнер с СУБД Mariadb 10.7
* [Phpmyadmin](https://hub.docker.com/r/phpmyadmin/phpmyadmin) - контейнер с Phpmyadmin

## 1. Установка
*Примечание: для работы окружения нужен установленный docker и docker compose*

Склонировать репозиторий

`git clone https://github.com/jurapro/web-base`

## 2. Настройка

Скопировать файл `.env.example` в `.env` и заполнить его:

1. PROJECT_NAME - название проекта. По-умолчанию `project`;
2. USER - логин пользователя под которым будет запущен веб-сервер в  контейнере с php. Если пустой, то логин текущий логин ползователя или `user` (зависит от операционной системы). Также такой пользователь будет создан для доступа к базе данных в контейнере с mariadb;
3. PASSWORD - пароль пользователя внутри контейнера с php. По-умолчанию `password`;
4. WEB_PORT - порт для веб-сервера. По-умолчанию `8080`;
5. DB_DRIVER - драйвер БД. По-умолчанию `mysql`;
6. DB_HOST - хост БД. Для БД на хост-машине из Docker используй `host.docker.internal`;
7. DB_PORT - порт БД. По-умолчанию `3306`;
8. DB_DATABASE - имя базы данных;
9. DB_USERNAME - пользователь БД;
10. DB_PASSWORD - пароль БД;
11. DB_AUTO_SETUP - если `true`, проект автоматически создаёт таблицы и демо-данные; для внешней готовой БД обычно нужно `false`;
12. DB_ROOT_PASSWORD - пароль `root` для контейнера MariaDB;
13. WEB_PORT_PHPMYADMIN - порт для phpmyadmin. По-умолчанию `8081`.

## 3. Запуск и остановка

Для запуска окружения необходимо в директории с `docker-compose.yml` выполнить командц `docker compose up -d`.

Для остановки окружения необходимо в директории с `docker-compose.yml` выполнить командц `docker compose down`.
