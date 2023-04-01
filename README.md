# Sistema de Locação

## Ambiente em Docker

#### Arquivo docker-compose.yml
```
services:
  php:
    build:
      context: .
      dockerfile: Dockerfile
    image: locacao-php
    container_name: locacao-php
    ports:
      - "80:80"
    volumes:
      - ./src:/var/www
    links:
      - db

  db:
    image: mysql:5.7
    container_name: locacao-mysql
    ports:
      - "3306:3306"
    volumes:
      - /var/lib/mysql
    environment:
      - MYSQL_DATABASE=locacao
      - MYSQL_ROOT_PASSWORD=L0c@c4O
```

#### Arquivo Dockerfile
```
FROM php:7.3-apache

WORKDIR /var/www/public

RUN buildDeps=" \
        default-libmysqlclient-dev \
        libbz2-dev \
        libmemcached-dev \
        libsasl2-dev \
    " \
    runtimeDeps=" \
        curl \
        git \
        nano \
        libfreetype6-dev \
        libicu-dev \
        libjpeg-dev \
        libldap2-dev \
        libmemcachedutil2 \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        libonig-dev \
        libgd-dev \
    " \
    && apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y $buildDeps $runtimeDeps \
    && docker-php-ext-install bcmath bz2 calendar iconv intl mbstring mysqli opcache pdo_mysql pdo_pgsql pgsql soap zip gd \
    && docker-php-ext-install gd \
    && docker-php-ext-install ldap \
    && docker-php-ext-install exif \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && pecl install memcached redis \
    && docker-php-ext-enable memcached.so redis.so \
    && apt-get purge -y --auto-remove $buildDeps \
    && rm -r /var/lib/apt/lists/* \
    && a2enmod rewrite

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && ln -s $(composer config --global home) /root/composer

ENV PATH=$PATH:/root/composer/vendor/bin COMPOSER_ALLOW_SUPERUSER=1
```

Executar ```docker-compose up --build --force-recreate```

Acessar container ```docker exec -it locacao-php /bin/bash```

Alterar em ```/etc/apache2/sites-available/000-default.conf``` para ```DocumentRoot /var/www/public```


