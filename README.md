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

## Frontend (local development)

Requirements: Node.js 24. Start the backend first.

```bash
cd frontend
npm ci
cp .env.example .env   # VITE_API_BASE_URL=http://localhost:8000/api
npm run dev
```

Open `http://localhost:5173` and log in with the demo account. The API address is read from `VITE_API_BASE_URL`, never hardcoded in components.

## Run everything with Docker Compose

Requirements: Docker with the Compose plugin.

```bash
cp .env.example .env    # then fill in APP_KEY, DB_PASSWORD, DB_ROOT_PASSWORD
docker compose up -d --build
docker compose ps
```

| Service | Image | URL |
|---|---|---|
| `frontend` | Nginx serving the Vue build, proxies `/api/` to `backend:8000` | http://localhost:8080 |
| `backend` | Laravel (`php artisan serve`), connects to `db:3306` | http://localhost:8000/api (for Postman) |
| `db` | MySQL 8.4, data in the `db_data` named volume | internal only |

The backend runs migrations and the seeder on every start, so the demo account is always available. Data stays after `docker compose down` and `docker compose up -d`. Use `docker compose down -v` only if you want to delete the database.

## CI/CD

`.github/workflows/ci.yml` runs on pushes to `main`, `dev`, `feature/**` and on pull requests to `main` and `dev`:

- `backend-test`: `php artisan test` in `php:8.4.27-cli`
- `frontend-build`: `npm ci` and `npm run build` on Node 24
- `deploy` (push to `main` only): builds both images and pushes them to `ghcr.io/aarmeliaa/uts-pl-24-538562-sv-24509-{backend,frontend}:<commit-sha>`

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
