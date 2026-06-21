# SongClub — Music Social Network

A social network centered around music where users share what they are currently listening to, discover songs from other users, like and favorite songs, and leave comments on posts.

**Stack:** PHP 8 · FastRoute · PDO · MariaDB · Vue 3 · Pinia · Vue Router · Vite · Bootstrap 5

---

## Quick Start (Docker)

### Requirements
- Docker Desktop (or Docker Engine + Docker Compose)

### Steps

```bash
# 1. Clone the repository
git clone <repo-url>
cd SongClub

# 2. Start all containers (backend + frontend + database)
docker compose up --build
```

Wait for all containers to be ready (about 30–60 seconds on first run).

| Service | URL |
|---|---|
| **Vue frontend** | http://localhost:5173 |
| **PHP backend / API** | http://localhost |
| **PHPMyAdmin** | http://localhost:8080 |

> The database schema and seed data are loaded automatically from `database/init.sql` when the MySQL container starts for the first time.

---

## Login Credentials

| Username | Email | Password | Role |
|---|---|---|---|
| `admin` | admin@songclub.com | `password` | Admin |
| `alice` | alice@songclub.com | `password` | User |

---

## Project Structure

```
SongClub/
├── app/                        # PHP backend (MVC)
│   ├── public/                 # Entry point (index.php), assets
│   └── src/
│       ├── Controllers/        # HTTP layer — one class per resource
│       ├── Services/           # Business logic
│       │   └── Interfaces/
│       ├── Repositories/       # Data access (PDO)
│       │   └── Interfaces/
│       ├── Models/             # Plain PHP data classes
│       ├── ViewModels/         # Data containers for PHP views
│       ├── Views/              # PHP templates (Bootstrap 5)
│       └── Framework/          # Base Controller and Repository classes
├── frontend/                   # Vue 3 SPA
│   └── src/
│       ├── pages/              # 8 page-level components
│       ├── components/         # Reusable components (Atomic Design)
│       │   ├── organisms/
│       │   └── molecules/
│       ├── stores/             # Pinia state management
│       ├── router/             # Vue Router with auth guards
│       └── utils/              # Axios instance with JWT interceptor
├── database/
│   └── init.sql                # Schema + seed data
├── docker-compose.yml
├── PHP.Dockerfile
└── Frontend.Dockerfile
```

---

## REST API Endpoints

### Authentication
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/login` | — | Login, returns JWT token |
| POST | `/api/auth/register` | — | Register new user, returns JWT token |
| GET | `/api/auth/me` | JWT | Get current user info |

### Songs
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/songs?artist=&page=&limit=` | — | List songs (filtering + pagination) |
| GET | `/api/songs/{id}` | — | Get single song |
| POST | `/api/songs` | JWT | Create song |
| PUT | `/api/songs/{id}` | JWT (owner/admin) | Update song |
| DELETE | `/api/songs/{id}` | JWT (owner/admin) | Delete song |

### Social Feed
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/feed?page=&limit=` | — | Paginated feed of all user posts |
| POST | `/api/posts` | JWT | Create a post (song_id + optional caption) |
| GET | `/api/posts/{id}/comments` | — | Get comments for a post |
| POST | `/api/posts/{id}/comments` | JWT | Add a comment to a post |

### Favorites & Likes
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/users/{id}/favorites?artist=&page=&limit=` | — | User's favorites (filtering + pagination) |
| GET | `/api/users/{id}/liked?artist=&page=&limit=` | JWT (owner/admin) | User's liked songs |
| POST | `/api/songs/{id}/favorite` | JWT | Toggle favorite on a song |
| POST | `/api/songs/{id}/like` | JWT | Toggle like on a song |

### Users & Admin
| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/api/users/{id}` | — | Get user profile |
| GET | `/api/users/search?q=` | JWT | Search users by username |
| GET | `/api/admin/users` | JWT (admin) | List all users |
| DELETE | `/api/admin/users/{id}` | JWT (admin) | Delete a user |

All paginated endpoints return:
```json
{
  "data": [...],
  "meta": { "page": 1, "limit": 10, "total": 50, "total_pages": 5 }
}
```

---

## Authentication

JWT tokens are generated on login and registration. The token must be sent with every protected request:

```
Authorization: Bearer <token>
```

Two roles are available:
- `user` — default role, can create songs and posts, like/favorite songs, comment
- `admin` — can additionally manage all users and edit/delete any song

---

## AI Disclosure

This project was developed with assistance from Claude (Anthropic) for code quality refactoring tasks, specifically:

- Extracting business logic from controllers into service methods
- Renaming API methods to use consistent verb+noun conventions (`showFeed`, `createPost`, `likeSong`, etc.)
- Adding helper methods to the base `Controller` class (`jsonPaged`, `requireJwtAdmin`) to reduce repetition
- Extracting private helpers within controllers (`loadPostsWithComments`, `validatePasswordChange`, `buildSong`)
- Moving input validation (email format) from controllers into the service layer

All architectural decisions, feature design, database schema, frontend components, and core implementation were written by the student. AI was used exclusively for refactoring already-working code, not for generating features or solving logic problems.
