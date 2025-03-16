# Rent Systema

# Equipment Rental Management System

This is a PHP-based system designed for managing equipment rentals. The application provides comprehensive features for managing clients, equipment, drivers, vehicles, waste management, financial transactions, and more.

## Features

### 1. **Registration**
- **Client**: Register new clients for equipment rental.
- **Equipment**: Add and manage available equipment for rental.
- **Driver**: Register drivers who handle the transportation of equipment.
- **Vehicle**: Manage the vehicles used for transporting equipment.
- **Waste**: Manage waste information associated with rentals.
- **Supplier**: Register suppliers for equipment and services.
- **Disposal Location**: Manage locations where waste is discarded.

### 2. **Control**
- **Rental**: Manage equipment rental orders, including rental dates and associated costs.
- **Quote**: Create and manage rental quotes before finalizing the rental.
- **Accounts Receivable**: Track payments to be received from clients.
- **Accounts Payable**: Track payments to be made to suppliers or partners.
- **Cash Flow**: Monitor the flow of cash within the business, tracking income and expenses.

### 3. **Reports**
- **Rental Report**: Generate reports related to rentals and equipment usage.
- **Financial Report**: Generate financial reports for cash flow, accounts receivable, and accounts payable.
- **Registration Report**: Reports on client, equipment, and supplier registrations.
- **Commission Report**: Track commissions associated with rentals and other business activities.

### 4. **Plans**
- **Subscription Plans**: Offer monthly subscription plans for clients to rent equipment and services, integrated with the financial system using Mercado Pago.

### 5. **Additional Features**
- **Receipt Printing**: Print receipts after finalizing a rental or quote.
- **Quote Approval**: Approve quotes before proceeding with equipment rental.
- **MTR (Waste Transport Manifest) Printing**: Print MTR documents for waste management during equipment transportation.
- **WhatsApp Notifications**: Send notifications via WhatsApp for updates on rentals, payments, or other key events.
- **API Logs**: Keep logs of all API interactions to track system activity.
- **Audit Logs**: Maintain detailed audit logs for actions taken by users in the system.

### 6. **User Management**
- **User Roles**: Two main user profiles:
  - **Administrator**: Admin profile with the ability to define permissions and manage system settings.
  - **Owner**: Higher-level profile with access to manage all companies, users, and system records.
  
### 7. **Email Notifications**
- **SMTP Email**: Send emails for notifications, confirmations, and updates related to rentals and other activities.

### 8. **User Manuals**
- **Documentation**: Provide user manuals to guide users on how to effectively use the system.

### 9. **Customer Support**
- **In-App Support**: Built-in support system for handling customer queries and issues.

--- 

## Installation

1. Clone the repository:
```bash
    git clone https://github.com/Henriquuepedro/locacao.git
```

2. Navigate to the project directory:
```bash
    cd locacao
```

3. Create the file `docker-compose.yml` and enter:
```bash
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

4. Create the file `Dockerfile` and enter:
```bash
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

5. Run to create the container:
```bash
    docker-compose up --build --force-recreate
```

6. Join in the container:
```bash
    docker exec -it locacao-php /bin/bash
```

7. Set up the environment variables. Copy the `.env.example` file to `.env` and update the necessary details (e.g., database, API credentials, SMTP settings):
```bash
    cp .env.example .env
```

8. Generate the application key:
```bash
    php artisan key:generate
```

The application should now be running at `http://localhost:8000`.

---

## Configuration the cron

```bash
apt-get update -y \
&& apt-get install cron -y \
&& echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1" >> /etc/cron.d/scheduler \
&& chmod 644 /etc/cron.d/scheduler \
&& crontab /etc/cron.d/scheduler
```

## Fixes to future

---

#### `Call to undefined function Intervention\\Image\\Gd\\imagecreatefromjpeg()`
```bash
RUN apt-get update && apt-get install -y \
libfreetype6-dev \
libjpeg62-turbo-dev \
libpng-dev \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) gd
```
---

#### Error 404
Update in `/etc/apache2/sites-available/000-default.conf` to `DocumentRoot /var/www/public`

---

#### Permission to the project if error is shown: `docker  - Cannot save \\wsl$\Ubuntu\home\... Unable to open the file for writing.`

```bash
sudo chown -R www-data:www-data {PROJECT}/
sudo chmod g+w {PROJECT}/
```

---


## Contributing

Feel free to fork this project and submit pull requests. If you encounter any bugs or have suggestions for improvements, please open an issue on GitHub.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
