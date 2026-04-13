# Tech Support Backend

Laravel backend API for the Tech Support system.

The backend is responsible for authentication, Google OAuth flow, user management, tickets, ticket chat, queue processing, and realtime updates through Laravel Reverb. The project uses session-based authentication through Laravel Sanctum and is designed to work together with the Vue frontend over HTTPS.

## Stack

- Laravel 13
- PHP 8.4
- MySQL
- Laravel Sanctum
- Laravel Socialite
- Laravel Reverb
- Docker
- Docker Compose
- Nginx

## Main Features

- Session-based authentication with Laravel Sanctum
- Registration and login by email and password
- Google OAuth login
- Required profile completion for new Google users
- CSRF protection for stateful frontend requests
- User profile management
- Admin user management
- Ticket creation, assignment, status updates, and editing
- Ticket chat
- Queue worker support
- Realtime events and private channels
- Role-based access control
- Rate limiting for login and registration routes

## Project Structure

- `app/` — main application code
- `app/Http/Controllers/` — API and auth controllers
- `app/Http/Requests/` — request validation classes
- `app/Http/Resources/` — API resources
- `app/Services/` — business logic services
- `app/Models/` — Eloquent models
- `routes/api.php` — API routes
- `routes/web.php` — web routes for Google redirect and callback
- `routes/channels.php` — broadcast channel authorization
- `config/services.php` — third-party integrations, including Google
- `config/sanctum.php` — Sanctum configuration
- `config/reverb.php` — Reverb configuration
- `docker-compose.dev.yml` — local development containers
- `docker-compose.prod.yml` — production containers
- `nginx.dev.conf` — nginx configuration for local development
- `nginx.prod.conf` — nginx configuration for production
- `tests/Feature/` — feature tests
- `tests/Unit/` — unit tests

## Authentication

The project uses Laravel Sanctum with cookie-based authentication.

### Standard auth flow

1. Frontend requests `/sanctum/csrf-cookie`
2. Backend sets CSRF and session cookies
3. Frontend sends login or register request with credentials
4. Backend creates authenticated session
5. Protected API routes use `auth:sanctum`

The frontend does not need to store bearer tokens in localStorage.

### Google auth flow

The backend also supports Google authentication through Laravel Socialite.

Flow:

1. Frontend opens `/auth/google/redirect`
2. Backend redirects user to Google
3. Google returns user to `/auth/google/callback`
4. Backend finds or creates a local user
5. Backend starts authenticated session
6. If Google user has no department yet, backend redirects frontend to `/auth/google/complete`
7. Frontend sends `POST /api/auth/google/complete-registration`
8. Backend saves department and password, after which the user can use the system normally

A Google user is considered incomplete until required profile data is filled in.

## Main Auth Endpoints

### Web routes

- `GET /auth/google/redirect`
- `GET /auth/google/callback`

### API routes

- `POST /api/auth/login`
- `POST /api/auth/register`
- `POST /api/auth/logout`
- `POST /api/auth/google/complete-registration`
- `GET /api/user`

## Realtime

Realtime updates are powered by Laravel Reverb.

Used for:

- user updates
- user deletion events
- forced logout events
- ticket updates
- ticket chat messages
- ticket list updates

Private channels are authorized through `routes/channels.php`.

Realtime is proxied through nginx over secure WebSocket.

## Requirements

- Docker
- Docker Compose
- Local MySQL server or another accessible MySQL instance
- Local HTTPS certificates for nginx
- Trusted local certificate in browser and system
- Valid Google OAuth credentials if Google login is enabled

## Local HTTPS

The backend is served behind nginx with HTTPS.

Expected local certificates:

- `docker/certs/local-cert.pem`
- `docker/certs/local-key.pem`

Main local backend URL:

- `https://127.0.0.1`

If you use self-signed certificates, trust them in your system and browser first. Otherwise cookies, redirects, and WSS may work incorrectly.

## Environment Setup

Create environment file:

```bash
cp .env.example .env
```

Fill in real values for:

- `APP_KEY`
- `APP_URL`
- `FRONTEND_URL`
- database connection
- Reverb keys
- Google OAuth credentials
- any production-specific secrets

Important Google variables:

```env
FRONTEND_URL=http://127.0.0.1
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://127.0.0.1/auth/google/callback
```

Important notes:

- do not commit real `.env` values
- rotate secrets if they were ever exposed
- keep `APP_DEBUG=false` outside local development
- for local HTTPS cookie auth, `SESSION_SECURE_COOKIE=true`
- for cross-site local auth flow, `SESSION_SAME_SITE=none` may be required depending on your setup

## Local Development

Start local containers:

```bash
docker compose -f docker-compose.dev.yml down -v --remove-orphans
docker compose -f docker-compose.dev.yml up -d --build
```

Useful checks:

```bash
docker compose -f docker-compose.dev.yml ps
docker logs -f laravel-app
docker logs -f laravel-reverb
docker logs -f laravel-queue
```

Clear Laravel caches inside the app container:

```bash
docker compose -f docker-compose.dev.yml exec app php artisan optimize:clear
```

Run migrations:

```bash
docker compose -f docker-compose.dev.yml exec app php artisan migrate
```

## Tests

Run tests inside the backend container:

```bash
docker exec -it laravel-app sh
php artisan test
```

Current backend tests cover:

- login and registration
- logout
- auth rate limiting
- current user profile
- Google redirect and callback behavior
- Google registration completion flow
- tickets and users feature flows

## Production Launch

Start production containers:

```bash
docker compose -f docker-compose.prod.yml down -v --remove-orphans
docker compose -f docker-compose.prod.yml up -d --build
```

Before production launch, make sure:

- `APP_DEBUG=false`
- secure secrets are set
- Google redirect URI matches production domain
- HTTPS certificates are valid
- database credentials are correct
- queue and Reverb are running

## Security Notes

Already covered in the current setup:

- secure session cookies
- Sanctum stateful authentication
- CSRF bootstrap endpoint
- login and register route rate limiting
- role checks on protected routes
- private broadcast channel authorization

Recommended operational checks:

- never expose real OAuth secrets in repository
- verify `CORS_ALLOWED_ORIGINS`
- verify `SANCTUM_STATEFUL_DOMAINS`
- trust local HTTPS certificate in browser
- make sure nginx proxies auth callback routes correctly
