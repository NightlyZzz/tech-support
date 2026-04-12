#!/usr/bin/env bash
set -e

usage() {
  echo "Usage: ./helper.sh [bash-app|bash-queue|bash-reverb|bash-nginx|logs-app|logs-queue|logs-reverb|logs-nginx]"
}

case "$1" in
  bash-app)
    docker exec -it laravel-app sh
    ;;
  bash-queue)
    docker exec -it laravel-queue sh
    ;;
  bash-reverb)
    docker exec -it laravel-reverb sh
    ;;
  bash-nginx)
    docker exec -it nginx sh
    ;;
  logs-app)
    docker logs -f laravel-app
    ;;
  logs-queue)
    docker logs -f laravel-queue
    ;;
  logs-reverb)
    docker logs -f laravel-reverb
    ;;
  logs-nginx)
    docker logs -f nginx
    ;;
  *)
    usage
    exit 1
    ;;
esac
