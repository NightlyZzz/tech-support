#!/usr/bin/env bash
set -eu

COMPOSE="docker compose"
DEV_FILE="-f docker-compose.dev.yml"
PROD_FILE="-f docker-compose.prod.yml"

usage() {
  echo "Usage: ./deploy.sh [dev|dev-down|prod|prod-down|cache|rebuild]"
}

validate_compose() {
  $COMPOSE "$1" config >/dev/null
}

case "${1:-}" in
  dev)
    validate_compose "$DEV_FILE"
    $COMPOSE $DEV_FILE up -d --build
    ;;
  dev-down)
    validate_compose "$DEV_FILE"
    $COMPOSE $DEV_FILE down -v --remove-orphans
    ;;
  prod)
    validate_compose "$PROD_FILE"
    $COMPOSE $PROD_FILE up -d --build
    ;;
  prod-down)
    validate_compose "$PROD_FILE"
    $COMPOSE $PROD_FILE down -v --remove-orphans
    ;;
  cache)
    validate_compose "$PROD_FILE"
    $COMPOSE $PROD_FILE exec app php artisan optimize:clear
    $COMPOSE $PROD_FILE exec app php artisan config:cache
    $COMPOSE $PROD_FILE exec app php artisan route:cache
    ;;
  rebuild)
    validate_compose "$PROD_FILE"
    $COMPOSE $PROD_FILE down -v --remove-orphans
    $COMPOSE $PROD_FILE up -d --build
    ;;
  *)
    usage
    exit 1
    ;;
esac
