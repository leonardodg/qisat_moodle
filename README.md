# Projeto Plataforma Moodle - QiSat

> Repositório contendo os arquivos referentes a plataforma moodle da qisat, assim como o container docker para efetuar a instalação do moodle, e banco de dados.

As instruções contidas neste documento são uteis para a instalação do projeto no ambiente.

![](icon_moodle.png)

## Requisitos

* GIT
* DOCKER

## Instalação

Baixe o repositório para a sua estação de trabalho:

``` sh
git clone https://github.com/qisat/moodle.git
```

    Outra forma de baixar o repositório:

``` sh
git clone git@gitlab.qisat.com.br:qisat/moodle.git
```

Comandos GIT extras.

``` sh
git config --global core.filemode false
git config --global core.autocrlf false
git checkout MOODLE_UNIVALI
git push origin MOODLE_UNIVALI:MOODLE_UNIVALI
```

> O Comando checkout e origin devem estar relacionados com a branch que você está baixando.
> exemplo:  altere MOODLE_UNIVALI para MOODLE_38_UNIVALI_DEV (caso essa seja a branch atual.)

Após baixar o repositório, será necessário configurar o projeto para ambiente de desenvolvimento.

Linux & GIT Bash:

``` sh
cd moodle
cp -r ./docker/php/php.ini-development ./docker/php/php.ini
```

Windows:

``` sh
cd moodle
copy ./docker/php/php.ini-development ./docker/php/php.ini
```

## URL localhost ( Ambiente de desenvolvimento)

https://local-moodle.qisat.dev

## Edição de arquivo hosts necessário para o funcionamento da url local do moodle

* Incluir no arquivo hosts a linha

    - 127.0.0.1 local-moodle.qisat.dev

LINUX:

``` sh
sudo nano /etc/hosts
```

Windows: 

> Necessário acessar o PowerShell / CMD como administrador

``` sh
cd C:\Windows\System32\drivers\etc\
notepad hosts
```

## Certificado no ambiente de desenvolvimento 

> Está sendo estudada uma solução para corrigir problemas de certificado no ambiente de desenvolvimento
> O Certificado está localizado na pasta ssl->docker do projeto (./docker/ssl)

 Instalação

* Windows: 
    - Localize o arquivo *.crt, clique com o botão direito do mouse, e em seguida "instalar certificado";
    - Defina se o certificado será instalado para o usuário windows logado, ou para todos os usuários windows (Este detalhe não é muito relevante, se a estação de trabalho tem somente um usuário atuando neste projeto), então avance;
    - Onde colocar o repositório: Clique em colocar todos os certificado no repositório a seguir, em seguida clique em procurar, e selecione "Autoridades de certificação Raiz Confiáveis", clique em ok, posteriormente avance;
    - verifique as informações, e conclua.

## Dados do banco de dados

* DB:       MariaDB
* Lib:      Native
* Prefix:   mdl_
* HOST:     moodle_38_db
* Name:     qisat_moodle38
* PORT:     3306
* User:     qisat
* Password: qisat 

## Dados MOODLE

* Login ADM:    admin
* Password:     Qisat.123

> Quando o projeto é criado, o moodle começa uma configuração limpa, e fica sob responsabilidade do desenvolvedor fazer as configurações citadas, porém, recomenda-se utilizar estas informações, por motivos de padronização.
