# Projeto Plataforma Moodle - QiSat 

- **Requisitos**:
* git
* docker 

- **Install**:
$ git clone git@gitlab.qisat.com.br:qisat/moodle.git

- **Start Guide**
$ copy ./docker/php/php.ini-development ./docker/php/php.ini
$ copy .copy-env .env
$ docker-compose up -d --build 

## CONFIG FILE .env

------- CONFIG DATABASE ------------
DB_ROOT_PASSWORD=qisat
DB_USER=qisat
DB_PASSWORD=qisat.123
DB_NAME=qisat_moodle38
DB_HOST=moodle_38_db
DB_TYPE=mariadb
DB_LIBRARY=native
DB_PREFIX=mdl_
------- PATH'S MAQUINA HOST ---------
PATH_HOST_WWW_MOODLE=./
PATH_HOST_MOODLE_DATA=../moodle_data
------- PATH'S MAQUINA CONTAINER ----
PATH_MOODLE_DATA=/var/www/moodledata
----- PORTAS MAQUINA HOST -----------
WWW_HOST_PORT=80
SSL_HOST_PORT=443
DB_HOST_PORT=3308
----- PORTAS MAQUINA CONTAINER -------
WWW_CTNR_PORT=80
SSL_CTNR_PORT=443
DB_CTNR_PORT=3306
------------ MOODLE ------------------
MOODLE_URL=https://local-moodle.qisat.dev

### Config Extra Git

$ git config --global core.filemode false
$ git config --global core.autocrlf false

$ git checkout MOODLE_UNIVALI
$ git push origin MOODLE_UNIVALI:MOODLE_UNIVALI
