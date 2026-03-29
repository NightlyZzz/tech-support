#!/bin/bash
set -e

LARAVEL="laravel-app"
VUE="vue-app"

usage() {
  echo "Usage: ./helper.sh [bash-laravel|bash-vue]"
}

case "$1" in
  bash-laravel)
    docker exec -it $LARAVEL bash
    ;;
  bash-vue)
    docker exec -it $VUE bash
    ;;
  build-front)
    docker exec -it $VUE "npm install && npm run build"
    ;;
  *)
    usage
    exit 1
    ;;
esac
