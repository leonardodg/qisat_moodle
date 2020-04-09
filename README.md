# Projeto Plataforma Moodle - QiSat 

- **Requisitos**:
* git
* docker 

- **Install**:
$ git clone git@gitlab.qisat.com.br:qisat/moodle.git

- **Run Start Guide**
$ copy .copy-env .env
$ docker-compose up -d --build 

## CONFIG FILE .env
MOODLE_URL=#LINK DE ACESSO - EXEMPLO=http://localhost:8082
DB_ROOT_PASSWORD=#SENHA ROOT DO BANCO
MOODLE_DB_USER=#NOME DO USER PARA ACESSO AO BANCO
MOODLE_DB_PASSWORD=#SENHA DO USER PARA ACESSO AO BANCO
MOODLE_DB_NAME=#NOME DA BASE DE DADOS
PATH_MOODLE_WWW=# CAMINHO DO DIRETORIO WWW
PATH_MOODLE_DATA=# CAMINHO DO DIRETORIO MOODLE DATA
PATH_MYSQL_DATA=# CAMINHO DO DIRETORIO DO BANCO
WWW_PORT=#PORTA PARA ACESSO WWW
MARIADB_PORT_NUMBER=#PORTA PARA ACESSO AO BANCO

### Config Extra Git


$ git config --global core.filemode false
$ git config --global core.autocrlf false

$ git checkout MOODLE_UNIVALI
$ git push origin MOODLE_UNIVALI:MOODLE_UNIVALI
