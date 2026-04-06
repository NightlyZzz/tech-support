# Tech Support Backend

Laravel backend API for the Tech Support system.  
The project provides authentication, profile management, user administration, tickets, ticket chat, queue processing, and realtime updates through Laravel Reverb.

## Stack

- Laravel 13
- PHP 8.4
- MySQL
- Laravel Sanctum
- Laravel Reverb
- Docker
- Docker Compose
- Nginx

## Features

- Token-based authentication
- User profile management
- Admin user management
- Ticket creation and editing
- Ticket chat
- Queue worker support
- Realtime events and private channels
- Role-based access control

## Project Structure

- `app/` — application code
- `routes/api.php` — API routes
- `routes/channels.php` — broadcast channel authorization
- `config/broadcasting.php` — broadcasting configuration
- `config/reverb.php` — Reverb configuration
- `docker-compose.dev.yml` — Docker Compose file for local launch
- `docker-compose.prod.yml` — Docker Compose file for production launch
- `nginx.dev.conf` — nginx configuration used by Docker
- `nginx.prod.conf` — nginx configuration used by Docker

## Requirements

- Docker
- Docker Compose
- Local MySQL server
- SSL certificates for local HTTPS

## HTTPS

The project is configured to run through HTTPS.

For local launch, make sure nginx certificates exist, for example:

- `docker/certs/local-cert.pem`
- `docker/certs/local-key.pem`

These certificates are used by nginx to serve the backend over HTTPS and to proxy realtime connections.

Main local URL:

- Backend: `https://127.0.0.1`

Realtime connections are proxied through nginx over HTTPS / WSS.

If you use self-signed local certificates, your browser may ask you to trust them first.

## Environment Setup

Create the environment file:

```bash
cp .env.example .env
```

## Deployment

### Dev

```bash
docker compose -f docker-compose.dev.yml down -v --remove-orphans
docker compose -f docker-compose.dev.yml up -d --build
```

### Prod

```bash
docker compose -f docker-compose.prod.yml down -v --remove-orphans
docker compose -f docker-compose.prod.yml up -d --build
```
