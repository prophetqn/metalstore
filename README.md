# Metal store

## Requirements

- PHP 8.2
- Laravel 9|10
- MySQL 8.0+ / PostgreSQL 9.2+
- PHP extensions: exif, intl, bcmath, GD, zip.

## Installation

### Clone the repo

```bash
git clone https://github.com/prophetqn/metalshop.git
```

```bash
cd metalshop
rm -rf .git
```

Then install composer dependencies

```bash
composer install
```

### Configure the Laravel app

Copy the `.env.example` file to `.env` and make sure the details match to your install.

```bash
cp .env.example .env
```

You have to set up the database config before the next step.

### Install the shop

```bash
php artisan store:install
```
It will take a while to seed all product data.
You will also need to set up your administrator's account information in this step.

## Finished

You are now installed!

- You can access the storefront at `http://<yoursite>`
- You can access the admin hub at `http://<yoursite>/hub`

## Update prices from RSS

Directly update prices by executing the following command.
```bash
php artisan store:price:update
```

Or you can run it on a schedule every 10 minutes.

```bash
php artisan schedule:work
```

You can change the RSS's configs at the config/metalshop.php.

## Mail 

The mail service only works if you set the correct mail configs in the .env file.
You can use mailcatcher/mailpit for local environment.
It will work in queue. So you need to excecute following command:
```bash
php artisan queue:work
```

## Issues
If you meet any problem, feel free to contact me via:
```
Email: hieunt456@gmail.com
Phone: (+84) 0935358558
```
