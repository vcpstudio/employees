## Installation

Get code from repository:

> git clone https://github.com/vcpstudio/employees.git .

Run installation from composer:

> composer install

Create .env file and generate key:

> cp .env.example .env

> php artisan key:generate

Edit .env file:

> nano .env

Running migrations:

> php artisan migrate

Running seeders:

> php artisan db:seed
