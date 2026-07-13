<div align="center">

# Bug Feedback Platform

**A modern role-based bug tracking & feedback management platform**

Built with **Symfony 7.4** | **PHP 8.2+** | **Doctrine ORM**

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![Symfony](https://img.shields.io/badge/Symfony-7.4-000000?style=for-the-badge&logo=symfony&logoColor=white)](https://symfony.com/)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

</div>

---

## Overview

Bug Feedback Platform streamlines the entire bug lifecycle — from **reporting** and **triaging** to **resolution** — within a secure, multi-role environment. Clients submit bug reports with screenshots and reproduction steps, developers investigate and resolve issues, and administrators maintain full control through a rich analytics dashboard.

---

## Features

### Admin
- **Analytics Dashboard** — real-time metrics: total bugs, open/in-progress/fixed counts, critical issues, unassigned bugs, and per-project breakdowns
- **User Management** — create, edit, deactivate, and delete user accounts with role assignment (Admin / Developer / Client)
- **Project Management** — full CRUD for projects with automatic cleanup of related bugs, comments, and screenshots
- **Bug Oversight** — manage, assign, and delete any bug report across the platform

### Developer
- **Assignment View** — see all bugs assigned to you
- **Status Updates** — progress bugs through the pipeline (Open → In Progress → Fixed / Rejected)
- **Full Bug Access** — browse and comment on any bug report

### Client / Tester
- **Bug Submission** — report bugs with title, description, steps to reproduce, expected/actual results, priority, and screenshot uploads
- **My Reports** — track the status of your submitted bugs
- **Comments** — collaborate on resolution directly within each report

---

## Architecture

```
┌──────────┐      ┌────────────┐      ┌──────────────┐
│  Project  │ 1──∞ │ BugReport  │ 1──∞ │  BugComment  │
└──────────┘      └────────────┘      └──────────────┘
                       ∞ │                    │ ∞
                         │ ∞                  │ ∞
                       ┌─────────────────────────┐
                       │          User            │
                       └─────────────────────────┘
```

| Entity | Description |
|---|---|
| **User** | Authenticated accounts with role-based access (Admin, Developer, Client) |
| **Project** | Software projects that contain bug reports |
| **BugReport** | Core entity — title, description, reproduction steps, priority, status, screenshot |
| **BugComment** | Threaded discussions on bug reports |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Symfony 7.4 |
| ORM | Doctrine ORM 3.6 |
| Database | SQLite (dev) / MySQL / MariaDB (prod) |
| Templating | Twig |
| Frontend | Vanilla CSS, Hotwired Turbo |
| Assets | Symfony AssetMapper + ImportMap |
| Testing | PHPUnit 13 |
| Migrations | Doctrine Migrations 4.0 |

---

## Security

- **Role-based access control** with hierarchical permissions
- **CSRF protection** on all forms
- **Password hashing** with bcrypt/argon2
- **User activation system** — admins can deactivate accounts
- **File upload validation** — type checking (JPG/PNG/WEBP), size limits (2MB), random filenames, path traversal protection
- **Sensitive environment files** excluded from version control

---

## Bug Lifecycle

```
  ┌────────┐     ┌──────────────┐     ┌────────┐
  │  Open  │ ──▶ │  In Progress │ ──▶ │  Fixed │
  └────────┘     └──────────────┘     └────────┘
       │                │                    │
       │                ▼                    │
       │          ┌───────────┐              │
       │          │ Rejected  │              │
       │          └───────────┘              │
       │                                     │
       └──────────────▶ ┌────────┐ ◀─────────┘
                        │ Closed │
                        └────────┘
```

Timestamps are automatically tracked: `openedAt`, `treatedAt`, `closedAt`.

---

## Filtering System

The bug list supports **7 filter parameters** for precise issue tracking:

| Filter | Description |
|---|---|
| `keyword` | Full-text search across title and description |
| `project` | Filter by project |
| `status` | Filter by status (Open, In Progress, Fixed, Rejected, Closed) |
| `priority` | Filter by priority (Low, Medium, High, Critical) |
| `developer` | Filter by assigned developer |
| `dateFrom` | Bugs created after this date |
| `dateTo` | Bugs created before this date |

---

## Installation

### Prerequisites

- PHP >= 8.2
- Composer
- SQLite (dev) or MySQL/MariaDB

### Setup

```bash
# Clone the repository
git clone https://github.com/guendouzyassir/bug-feedback-platform.git
cd bug-feedback-platform

# Install dependencies
composer install

# Create database and run migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Load fixture data (optional)
php bin/console doctrine:fixtures:load

# Start the development server
php bin/console server:start
```

### Default Test Accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | `password` |
| Developer | `dev@example.com` | `password` |
| Client | `client@example.com` | `password` |

---

## Project Structure

```
src/
├── Controller/
│   ├── Admin/          # Admin dashboard, user & project management
│   ├── Client/         # Client dashboard
│   └── Developer/      # Developer dashboard
├── Entity/             # Doctrine entities (User, Project, BugReport, BugComment)
├── Enum/               # BugStatus, BugPriority enums
├── Form/               # Form types
├── Repository/         # Custom query builders with filter support
├── Security/           # UserChecker for account activation
└── Service/            # FileUploader service

templates/
├── admin/              # Admin dashboards and CRUD views
├── bug_report/         # Bug listing, creation, detail, and management
├── client/             # Client dashboard
├── developer/          # Developer dashboard
└── security/           # Login page
```

---

<div align="center">

**Built as an internship project**

</div>
