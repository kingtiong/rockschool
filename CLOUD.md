# Cloud instructions

## Setup
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build

## Database
php artisan migrate --seed

## Run
php artisan serve

## Tests
Not configured.
