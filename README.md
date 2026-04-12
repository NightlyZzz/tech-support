# Tech Support Backend

Laravel backend API for the Tech Support system.

The backend handles authentication, user management, tickets, ticket chat, queue processing, and realtime updates through Laravel Reverb.

## Stack

- Laravel 13
- PHP 8.4
- MySQL
- Laravel Sanctum
- Laravel Reverb
- Docker
- Docker Compose
- Nginx

## Main Features

- Cookie-based authentication with Laravel Sanctum
- CSRF protection for stateful frontend requests
- User profile management
- Admin user management
- Ticket creation and editing
- Ticket chat
- Queue worker support
- Realtime events and private channels
- Role-based access control
- Rate limiting for login and registration

## Project Structure

- `app/` — application code
- `routes/api.php` — API routes
- `routes/channels.php` — broadcast channel authorization
- `config/broadcasting.php` — broadcasting configuration
- `config/reverb.php` — Reverb configuration
- `docker-compose.dev.yml` — Docker Compose file for local launch
- `docker-compose.prod.yml` — Docker Compose file for production launch
- `nginx.dev.conf` — nginx configuration for development
- `nginx.prod.conf` — nginx configuration for production

## Authentication

The project uses **Laravel Sanctum with cookie-based authentication**.

Frontend flow:

1. Frontend requests `/sanctum/csrf-cookie`
2. Backend sets CSRF and session cookies
3. Frontend sends login or register request with credentials
4. Backend creates authenticated session
5. Protected API routes use `auth:sanctum`

This means the frontend does **not** need to store API tokens in localStorage.

## Realtime

Realtime updates are powered by **Laravel Reverb**.

Used for:

- user updates
- user deletion/logout events
- ticket updates
- ticket chat messages
- ticket list updates

Private channels are authorized through `routes/channels.php`.

## Requirements

- Docker
- Docker Compose
- Local MySQL server or another accessible MySQL instance
- Local HTTPS certificates for nginx

## Local HTTPS

The backend is served behind nginx with HTTPS.

Expected local certificates:

- `docker/certs/local-cert.pem`
- `docker/certs/local-key.pem`

Main local backend URL:

- `https://127.0.0.1`

Realtime is proxied through nginx over WSS.

If you use self-signed certificates, trust them in your system and browser first.

## Environment Setup

Create environment file:

```bash
cp .env.example .env
```

Then fill in real values for:

- database connection
- reverb keys
- app key
- any production-specific credentials

Important:

- do not commit real `.env` values
- rotate secrets if they were ever exposed
- keep `APP_DEBUG=false` outside local development

## Local Development

Start containers:

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

Run tests inside the app container:

```bash
docker exec -it laravel-app sh
php artisan test
```

## Production Launch

Start production containers:

```bash
docker compose -f docker-compose.prod.yml down -v --remove-orphans
docker compose -f docker-compose.prod.yml up -d --build
```

## Security Notes

Already covered in the current setup:

- secure session cookies
- Sanctum stateful auth
- CSRF bootstrap endpoint
- auth route rate limiting
- role checks on protected routes
- private broadcast channel authorization
