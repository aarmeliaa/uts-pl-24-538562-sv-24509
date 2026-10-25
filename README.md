# Media Tracker

A consumed media tracker for films, series, YouTube videos, music, anime, comics, and books.

- `backend/`: Laravel 12 REST API with Sanctum token authentication
- `frontend/`: Vue.js client that consumes the API
- `postman/`: Postman Collection for every API endpoint

## Backend (local development)

Requirements: PHP 8.4 and Composer. The local database is SQLite.

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

The API runs at `http://localhost:8000/api`. Run the tests with `php artisan test`.

Demo account (created by the seeder): `demo@example.com` / `password`

## API endpoints

All endpoints except `POST /api/login` need the header `Authorization: Bearer <token>`.

| Method | Path | Description |
|---|---|---|
| POST | `/api/login` | Returns a token (`422` invalid input, `401` wrong credentials) |
| POST | `/api/logout` | Revokes the current token |
| GET | `/api/user` | Logged-in user |
| GET | `/api/media` | List own media |
| POST | `/api/media` | Create media (`201`, `422` on validation error) |
| GET | `/api/media/{id}` | Show one media |
| PUT/PATCH | `/api/media/{id}` | Update media |
| DELETE | `/api/media/{id}` | Delete media |

Media fields: `title`, `type` (`film`, `series`, `youtube`, `music`, `anime`, `comic`, `book`, `other`), `status` (`planned`, `in_progress`, `completed`, `dropped`), `rating` (1-10, optional), `notes` (optional), `consumed_at` (date, optional).

## Postman

Import `postman/uts-pl-media-tracker.postman_collection.json`, run **Login** first (it stores the token automatically), then run the other requests.
