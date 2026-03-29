#!/bin/bash
set -e

COMPOSE="docker compose"
DEV="-f docker-compose.dev.yml"
PROD="-f docker-compose.prod.yml"

usage() {
  echo "Usage: ./deploy.sh [dev|dev-down|prod|prod-down|build-front|cache|rebuild]"
}

case "$1" in
  dev)
    $COMPOSE $DEV up -d --build
    ;;
  dev-down)
    $COMPOSE $DEV down
    ;;
  prod)
    $COMPOSE $PROD up -d --build
    ;;
  prod-down)
    $COMPOSE $PROD down
    ;;
  build-front)
    $COMPOSE $PROD run --rm node sh -c "npm install && npm run build"
    ;;
  cache)
    $COMPOSE $PROD exec app php artisan config:clear
    $COMPOSE $PROD exec app php artisan config:cache
    $COMPOSE $PROD exec app php artisan view:clear
    $COMPOSE $PROD exec app php artisan route:clear
    ;;
  rebuild)
    $COMPOSE $PROD down -v --remove-orphans
    $COMPOSE $PROD up -d --build
    ;;
  *)
    usage
    exit 1
    ;;
esac
