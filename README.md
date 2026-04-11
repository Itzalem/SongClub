# SongClub — Social Music Web App

SongClub is a PHP MVC social network where users share the last song they listened to, discover music from others, like and favorite songs, and leave comments on profiles.

Built on the Docker template below with: **PHP 8 · FastRoute · PDO + MariaDB · Bootstrap 5**

---

## Docker Template

This repository provides a starting template for PHP application development.

It contains:
* NGINX webserver
* PHP FastCGI Process Manager with PDO MySQL support
* MariaDB (GPL MySQL fork)
* PHPMyAdmin
* Composer
* Composer package [nikic/fast-route](https://github.com/nikic/FastRoute) for routing

---

## Setup

1. Install Docker Desktop on Windows or Mac, or Docker Engine on Linux.
2. Clone the project.
3. Start the containers:

```bash
docker compose up
```

4. Install Composer dependencies:

```bash
docker compose run --rm php composer install
```

5. Initialize the database — open [localhost:8080](http://localhost:8080) (PHPMyAdmin, credentials: `developer` / `secret123`), select the **SQL** tab, and paste the contents of `database/init.sql`.

The app is now available at [http://localhost](http://localhost).

**Seeded accounts** (password: `password`):
| Username | Role  |
|----------|-------|
| admin    | admin |
| alice    | user  |

---

## Usage

### Composer Autoload

Namespace `App\\` is mapped to `app/src/` via PSR-4 autoloading.

If you add new classes or change namespaces, regenerate the autoloader:

```bash
docker compose run --rm php composer dump-autoload
```

### PHPMyAdmin

Database administration at [localhost:8080](http://localhost:8080).
Credentials: `developer` / `secret123`

### Stopping the containers

```bash
docker compose down
```

---

## Features

- Register / login / logout with hashed passwords
- Post "last song I listened to" with an optional caption
- Like and favorite songs (AJAX — no page reload)
- Comment on other users' last-listened posts (AJAX)
- Live user search in the navbar
- Export your favorite songs as a JSON file
- Admin panel to manage users (`/admin/users`)
- Song CRUD (create, edit, delete — owner or admin only)

---

## MVC Architecture

```
app/src/
├── Controllers/   — HTTP layer, one class per resource
├── Services/      — Business logic, depend on repository interfaces
├── Repositories/  — Data access (PDO), implement interfaces
│   └── Interfaces/
├── Models/        — Plain PHP classes representing DB rows
├── ViewModels/    — Data containers passed to views
├── Views/         — PHP templates (Bootstrap 5)
└── Framework/     — Base Controller and Repository classes
```

### OOP concepts demonstrated

| Concept | Where |
|---|---|
| **Inheritance** | `Controller` (base) → all controllers; `Repository` (base) → all repositories; `Interaction` (model) → `Like` |
| **Encapsulation** | `private $connection` in `Repository`; private repo fields in all services |
| **Interfaces** | `IUserRepository`, `IUserService`, `ISongRepository`, `IPostRepository`, `ICommentRepository`, `IInteractionRepository` — used as type hints between every layer |
| **Polymorphism** | `InteractionRepository` handles favorites and likes via `ESongType` enum; all service constructors accept the interface, not the concrete class |
| **Dependency injection** | Constructor injection in every service and controller |

---

## WCAG Accessibility

The application follows WCAG 2.1 Level AA guidelines:

- **Semantic HTML** — `<header>`, `<nav>`, `<main>`, `<footer>`, `<table>` used throughout (`app/src/Views/partials/header.php`, `app/src/Views/admin/users.php`)
- **Form labels** — every `<input>` and `<textarea>` has a matching `<label for="...">` (`app/src/Views/auth/login.php`, `app/src/Views/auth/register.php`, `app/src/Views/songs/create.php`, `app/src/Views/songs/edit.php`)
- **Colour contrast** — olive `#3a4a28` on cream `#f5f0e8` exceeds 7:1 ratio (`app/public/assets/css/main.css`)
- **Keyboard navigation** — all interactive elements are native `<button>` or `<a>` elements, reachable by Tab
- **No AJAX-only content** — comments and interactions degrade: the comment form submits only to logged-in non-owners; like/favorite buttons are hidden when not logged in

---

## GDPR Compliance

- **Password hashing** — passwords are never stored in plain text; `password_hash(PASSWORD_BCRYPT)` is applied in `UserService::register()` (`app/src/Services/UserService.php:67`)
- **Minimal data collection** — only username, email, and optional bio are collected; no tracking cookies or third-party analytics
- **Data deletion** — admin can delete any user account via `/admin/users`; all related posts, comments, likes, and favorites are removed automatically via `ON DELETE CASCADE` in the database schema (`database/init.sql`)
- **No plain-text transmission** — passwords are never echoed, logged, or returned in API responses; API search endpoint (`UserController::search`) returns only `id`, `username`, and `bio` — never email or password hash
