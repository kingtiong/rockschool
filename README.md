## Music School Portal (Laravel + MySQL)

Student / teacher / management portal for:

- schedules (with colored status badges)
- student payments (bank transfer + slip upload)
- teacher earnings (profit sharing) + payout tracking

Core requirements and rules live in `AGENTS.md`.

### Tech

- PHP + Laravel
- MySQL (production target)
- Blade + Tailwind (clean UI)

### Quick start (local)

1) Install PHP dependencies:

```bash
composer install
```

2) Create env file and set DB credentials:

```bash
cp .env.example .env
php artisan key:generate
```

3) Install/build frontend assets:

```bash
npm install
npm run build
```

4) Run migrations + seed demo data:

```bash
php artisan migrate --seed
```

5) Run the app:

```bash
php artisan serve
```

### Demo accounts (seeded)

All demo users use password: `password`

- management: `management@example.com`
- teacher: `teacher@example.com`
- student: `student@example.com`

### Current UI

- `Schedule` page: `/schedule`
  - shows lessons
  - colored status badges: scheduled / completed / postponed / missed
  - teacher can mark a lesson “completed”
