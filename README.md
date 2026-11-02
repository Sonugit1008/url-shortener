# URL Shortener

## Setup

```bash
git clone <repo-url>
cd url-shortener
composer install
cp .env.example .env
php artisan key:generate
```

Create a database `url_shortener` and set it in `.env`:

```env
DB_DATABASE=url_shortener
DB_USERNAME=root
DB_PASSWORD=
```

Run:

```bash
php artisan migrate --seed
php artisan serve
```

Open http://127.0.0.1:8000

SuperAdmin login:

- Email: superadmin@gmail.com
- Password: 12345678

## Tests

```bash
php artisan test
```
