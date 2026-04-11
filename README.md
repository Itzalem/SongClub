# SongClub — Social Music Web App

SongClub is a PHP MVC social network where users share the last song they listened to, discover music from others, like and favorite songs, and leave comments on profiles.

Built with: **PHP 8 · FastRoute · PDO + MariaDB · Bootstrap 5**

---

## Docker Setup

1. Install Docker Desktop.
2. Clone the project.
3. Start the containers:

```bash
docker compose up
```

4. Install Composer dependencies:

```bash
docker compose run --rm php composer install
```

5. Initialize the database — open [localhost:8080](http://localhost:8080) (PHPMyAdmin, credentials: `developer` / `secret123`), select the **songclub** database, click the **SQL** tab, and paste the contents of `database/init.sql`.

The app is now available at [http://localhost](http://localhost).

**Seeded accounts** (password: `password`):
| Username | Role  |
|----------|-------|
| admin    | admin |
| alice    | user  |

---

## Features

- Register / login / logout with hashed passwords (bcrypt)
- Post "last song I listened to" with an optional caption
- Like and favorite songs (AJAX — no page reload)
- Comment on other users' last-listened posts (AJAX — no page reload)
- Live user search in the navbar (AJAX)
- Export your favorite songs as a JSON file
- Admin panel to manage users (`/admin/users`)
- Song CRUD (create, edit, delete — owner or admin only)
- JSON API endpoints for songs and favorites

---

## MVC Architecture

```
app/src/
├── Controllers/   — HTTP layer, one class per resource
├── Services/      — Business logic, depend on repository interfaces
│   └── Interfaces/
├── Repositories/  — Data access (PDO prepared statements), implement interfaces
│   └── Interfaces/
├── Models/        — Plain PHP classes representing database rows
├── ViewModels/    — Data containers passed to views (e.g. ProfileVm)
├── Views/         — PHP templates (Bootstrap 5)
│   └── Partials/
└── Framework/     — Base Controller and Repository classes
```

### OOP Concepts Demonstrated

| Concept | Where |
|---|---|
| **Inheritance** | `App\Framework\Controller` (base) → all controllers; `App\Framework\Repository` (base) → all repositories |
| **Encapsulation** | Private `$userRepository`, `$songRepository` etc. in every service; `private` add/remove methods in `InteractionRepository` |
| **Interfaces** | `IUserRepository`, `IUserService`, `ISongRepository`, `ISongService`, `IPostRepository`, `IPostService`, `ICommentRepository`, `IFavoriteService`, `IInteractionRepository` — used as type hints between every layer |
| **Polymorphism** | `InteractionRepository` handles both favorites and likes via the `ESongType` enum; services accept interface types, not concrete classes |
| **Dependency injection** | Every service accepts its repository through the constructor; `PostService(IPostRepository)`, `FavoriteService(IInteractionRepository)`, etc. |

### Key Files

| Pattern | File |
|---|---|
| Entry point + FastRoute dispatcher | `app/public/index.php` |
| Base controller (`render`, `requireAuth`, `json`) | `app/src/Framework/Controller.php` |
| Base repository (`getConnection` via PDO) | `app/src/Framework/Repository.php` |
| Interaction polymorphism (favorites + likes) | `app/src/Repositories/InteractionRepository.php` |
| Profile view model | `app/src/ViewModels/ProfileVm.php` |

---

## API Endpoints

| Method | URL | Description |
|---|---|---|
| `GET` | `/api/songs` | All songs as JSON |
| `GET` | `/api/favorites/{userId}` | A user's favorite songs as JSON (downloadable) |
| `GET` | `/api/users/search?q=` | Live user search results as JSON |
| `POST` | `/favorites/toggle` | Toggle a favorite (AJAX) |
| `POST` | `/likes/toggle` | Toggle a like (AJAX) |
| `POST` | `/comments/store` | Post a comment (AJAX) |

---

## WCAG 2.1 Accessibility

The application follows WCAG 2.1 Level AA guidelines:

- **Semantic HTML** — `<header>`, `<nav>`, `<main>`, `<footer>` used throughout
  → `app/src/Views/Partials/header.php`, `app/src/Views/Partials/footer.php`
- **Form labels** — every `<input>` and `<textarea>` has a matching `<label for="...">` with explicit association
  → `app/src/Views/Login.php`, `app/src/Views/Register.php`, `app/src/Views/Songs/CreateSong.php`, `app/src/Views/Songs/EditSong.php`
- **Colour contrast** — olive `#3a4a28` on cream `#f5f0e8` exceeds 7:1 contrast ratio (WCAG AAA)
  → `app/public/Assets/css/main.css`
- **Keyboard navigation** — all interactive elements are native `<button>` or `<a>` elements, naturally reachable via Tab
- **Responsive design** — Bootstrap 5 grid (`col-12 col-sm-6 col-md-4`) adapts to mobile, tablet, and desktop
- **No AJAX-only content** — comments degrade gracefully: the form is only shown to logged-in non-owners, so the feature is naturally gated

---

## GDPR Compliance

- **Password hashing** — passwords are never stored in plain text; `password_hash(PASSWORD_BCRYPT)` is applied in `UserService::register()` before the repository saves the record
  → `app/src/Services/UserService.php` (line ~48)
- **Minimal data collection** — only username, email, and an optional bio are collected; no tracking cookies, analytics, or third-party services
- **Data deletion** — admin can delete any user account via `/admin/users`; all related posts, comments, likes, and favorites cascade automatically via `ON DELETE CASCADE`
  → `database/init.sql`
- **No sensitive data in API responses** — the search API (`/api/users/search`) returns only `id`, `username`, and `bio` — never email or password hash
  → `app/src/Controllers/UserController.php`, `search()` method
- **No plain-text transmission** — passwords are never echoed, logged, or returned anywhere in the application
