# Plataform Moodle - Company QiSat

## 💻 Desciption
**(DEPRECATED)**
Plataform used to company QiSat as system e-Learn intregation with Website ( FrontEnd - AngularJS ) and painel manage E-commerce ( CakePHP & API )

<img width="800" height="600" alt="homepage" src="https://github.com/leonardodg/website/blob/main/src/images/qisat_moodle.png?raw=true">

## 📋 Specification

- Moodle Version: 2.9.3 (Build: 20151109)
- PHP Version: PHP 5.6.40 (cli) (built: Jan 23 2019 00:10:05)
- Server version: Apache/2.4.25 (Debian)
- Docker: Debian GNU/Linux 9.13 (stretch)
- MySQL: mysql  Ver 14.14 Distrib 5.7.44

## 🚀 Quick Start

### Prerequisites
- [Git](https://git-scm.com/book/en/v2/Getting-Started-Installing-Git)
- [Docker](https://www.docker.com/products/docker-desktop/)


### Installation and Running

1. Clone all projects repository:
```bash
   git clone https://github.com/leonardodg/qisat_docker.git docker
   git clone https://github.com/leonardodg/qisat_moodle.git moodle
   git clone https://github.com/leonardodg/qisat_ecommerce.git ecommerce
   git clone https://github.com/leonardodg/qisat_website.git website
```

2. Copy Config:
```bash
   cd moodle
   cp config-dist.php config.php
```

3. Run docker:
```bash
   cd docker
   docker compose up -d --build
```

5. Install dependencies:
```bash
   cd moodle
   npm install
   composer install
   composer dump-autoload
```

6. Access the website in your browser:
```   https://moodle.qisat.local/ ```


## 🛠 Project Structure

```
QiSat
├── docker
├── ecommerce
├── moodle
├── moodledata
└── website
```

## 🌐 Links

- [docker](https://github.com/leonardodg/qisat_docker) - branch: master
- [ecommerce](https://github.com/leonardodg/qisat_ecommerce) - branch: master
- [moodle](https://github.com/leonardodg/qisat_moodle) - branch: MOODLE_29_QISAT
- [website](https://github.com/leonardodg/qisat_website) - branch: master

 ## 🤝 Contributing
- Teams Developer QiSat

 ## 📮 Contact
- LeonardoDG - [@le0dg](https://www.linkedin.com/in/le0dg)
- Website Link: [https://leodg.dev](https://leodg.dev)