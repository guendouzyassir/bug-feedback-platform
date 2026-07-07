# Bug Report & Feedback Management Platform

A web application built with PHP and the Symfony framework for managing bug reports, client feedback, developer assignments, and progress tracking.

## Description

This platform is designed for a company that builds web and mobile applications. Clients and testers can report bugs, developers can follow and update assigned issues, and administrators can manage projects and monitor progress from a dashboard.

## Features

- Authentication and logout
- Role-based access control
- Admin dashboard with statistics
- Project/application management
- Bug report creation and tracking
- Bug priorities: Low, Medium, High, Critical
- Bug statuses: Open, In Progress, Fixed, Rejected, Closed
- Admin assignment of bugs to developers
- Developer status updates for assigned bugs
- Comments inside bug reports
- Screenshot upload for bug reports
- Search and filters by keyword, project, status, priority, developer, and date range
- Secure screenshot access through Symfony routes

## User Roles

| Role | Permissions |
| --- | --- |
| Admin | Manage projects, view all bugs, assign developers, update priority/status, view statistics |
| Developer | View bugs, update status for assigned bugs, add comments |
| Client/Tester | Create bug reports, upload screenshots, view own bugs, add comments |

## Tech Stack

- PHP 8.4
- Symfony 7.4 LTS
- Twig
- Doctrine ORM
- Symfony Security
- Symfony Forms
- Symfony Validator
- SQLite for local development

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

If Symfony chooses another port, use the URL shown by:

```bash
symfony server:status
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
| Developer | developer2@example.com |
| Client/Tester | client@example.com |
| Client/Tester | tester@example.com |

## Main Routes

| Page | Route |
| --- | --- |
| Login | `/login` |
| Admin dashboard | `/admin/dashboard` |
| Project management | `/admin/projects` |
| Bug list | `/bugs` |
| New bug report | `/bugs/new` |
| Bug detail | `/bugs/{id}` |
| Manage bug | `/bugs/{id}/manage` |

## Uploads

Screenshots are stored outside the public directory:

```text
var/uploads/screenshots/
```

Only the stored filename is saved in the database. Screenshots are viewed through protected Symfony routes, so normal bug access rules still apply.

Allowed screenshot formats:

- JPG
- PNG
- WEBP

Maximum file size:

```text
2 MB
```

## Security Features

- Password hashing through Symfony Security
- CSRF protection on forms
- Server-side validation
- Role-based access control
- Secure file upload validation
- Screenshots stored outside `public/`
- Controller-level permission checks for viewing, managing, and status updates

## Testing

Run:

```bash
php bin/console lint:container
php bin/console lint:twig templates
php bin/console doctrine:schema:validate
php bin/phpunit
composer validate --strict
```

## Project Structure

```text
src/
├── Controller/
├── DataFixtures/
├── Entity/
├── Enum/
├── Form/
├── Repository/
└── Service/

templates/
├── admin/
├── bug_report/
├── client/
├── developer/
└── security/

docs/
├── DEMO_SCRIPT.md
├── INTERNSHIP_REPORT.md
└── MANUAL_TEST_PLAN.md
```

## Future Improvements

- Pagination for long bug lists
- Email notifications
- CSV export
- Dashboard charts
- More automated tests
- User management screen for admins

## Author

Internship project by Yassir.
