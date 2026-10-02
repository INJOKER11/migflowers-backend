#!/usr/bin/env bash
#
# Production deploy. Runs on the server, inside the checkout that
# docker-compose.prod.yml bind-mounts into the containers — the CI workflow
# has already done `git pull` by the time this starts, so this file is
# always the version being deployed.
#
# Safe to run by hand: `bash deploy/deploy.sh` from the repo root.

set -euo pipefail

cd "$(dirname "$0")/.."

# Older servers only have the standalone `docker-compose`; override with
# COMPOSE="docker-compose -f docker-compose.prod.yml" if so.
COMPOSE=${COMPOSE:-"docker compose -f docker-compose.prod.yml"}

echo "→ Deploying $(git rev-parse --short HEAD): $(git log -1 --pretty=%s)"

# Rebuilds only when docker/php/Dockerfile changed; otherwise the layer
# cache makes this a no-op.
$COMPOSE build app queue
$COMPOSE up -d --remove-orphans

# vendor/ is not in git and lives in the bind mount, so it is installed
# in place, inside the container, with the container's PHP.
$COMPOSE exec -T app composer install --no-dev --optimize-autoloader --no-interaction --no-progress

$COMPOSE exec -T app php artisan migrate --force
# Not `artisan optimize`: its view:cache step fails because the repo has no
# resources/views (the admin's views come from Filament inside vendor/).
$COMPOSE exec -T app php artisan config:cache
$COMPOSE exec -T app php artisan route:cache
$COMPOSE exec -T app php artisan event:cache

# php.ini sets opcache.validate_timestamps=0: php-fpm never notices changed
# files on its own and would keep serving the old code. USR2 is a graceful
# reload — requests in flight finish, and the opcache starts empty.
$COMPOSE exec -T app kill -USR2 1

# The worker exits after its current job and `restart: unless-stopped`
# brings it back on the new code.
$COMPOSE exec -T app php artisan queue:restart

echo "✓ Deployed"
