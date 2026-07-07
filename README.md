# Bug Report & Feedback Management Platform

Symfony internship project for managing client/tester bug reports, developer assignments, and admin progress tracking.

## Current Status

Week 1 baseline is implemented:

- Symfony 7.4 LTS web app
- SQLite local database
- Doctrine entities for `User`, `Project`, `BugReport`, and `BugComment`
- Role-based authentication for Admin, Developer, and Client/Tester
- Demo dashboards for each role
- Demo fixtures with users, projects, one bug report, and one comment

## Requirements

- PHP 8.2 or higher
- Composer
- Symfony CLI
- SQLite PHP extension

## Installation

```bash
composer install
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
symfony serve -d --no-tls
```

Open:

```text
http://127.0.0.1:8000
```

## Demo Accounts

All demo accounts use:

```text
password123
```

| Role | Email |
| --- | --- |
| Admin | admin@example.com |
| Developer | developer@example.com |
| Client/Tester | client@example.com |

## Week 2 Target

- Admin project management CRUD
- Client bug report creation
- Bug list and detail pages
- Comments inside bug reports
- Screenshot upload foundation
