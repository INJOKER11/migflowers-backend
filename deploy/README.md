# Автодеплой

`.github/workflows/ci-cd.yml`:

- **каждый PR** — тесты на PostgreSQL 16;
- **push в `main`** (то есть влитый PR) — тесты, и если они зелёные, деплой:
  GitHub заходит на сервер по SSH, делает `git pull` и запускает
  `deploy/deploy.sh`.

Деплой также можно запустить вручную: Actions → CI/CD → Run workflow (ветка `main`).

## Что делает `deploy/deploy.sh`

1. Пересобирает образ, если менялся `docker/php/Dockerfile`, и поднимает контейнеры
   из `docker-compose.prod.yml`.
2. `composer install --no-dev` внутри контейнера `app`.
3. `php artisan migrate --force`.
4. Кэширует конфиг, маршруты и события.
5. Мягко перезагружает php-fpm (`kill -USR2 1`) — без этого из-за
   `opcache.validate_timestamps=0` продолжал бы работать старый код.
6. `queue:restart` — воркер очереди перезапускается на новом коде.

## Настройка (один раз)

### На сервере

1. Код должен лежать в git-клоне этого репозитория, из которого запускается
   `docker compose -f docker-compose.prod.yml` (тот же каталог, что монтируется в
   контейнеры). `git pull` в нём должен работать без пароля — например, через
   deploy key с доступом на чтение (GitHub → Settings → Deploy keys).
2. Пользователь, под которым заходит GitHub, должен уметь запускать `docker`
   без `sudo` (состоять в группе `docker`).
3. Создайте для деплоя отдельный SSH-ключ:
   ```bash
   ssh-keygen -t ed25519 -f ~/.ssh/github_deploy -N "" -C "github-actions"
   cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
   cat ~/.ssh/github_deploy   # приватный ключ — в секрет SSH_PRIVATE_KEY
   ```

### В GitHub

Settings → Environments → создать окружение **`production`** и добавить в него
секреты:

| Секрет            | Пример                          |
|-------------------|---------------------------------|
| `SSH_HOST`        | `203.0.113.10` или домен        |
| `SSH_USER`        | `deploy`                        |
| `SSH_PRIVATE_KEY` | содержимое `~/.ssh/github_deploy` |
| `SSH_PORT`        | `22` (необязательно)            |
| `DEPLOY_PATH`     | `/home/deploy/migflowers-backend` |

В окружении `production` можно включить **Required reviewers** — тогда каждый
деплой будет ждать нажатия кнопки «Approve».

## Если на сервере старый `docker-compose`

Скрипт вызывает `docker compose` (v2). Для старого `docker-compose` замените в
workflow последнюю строку на
`COMPOSE="docker-compose -f docker-compose.prod.yml" bash deploy/deploy.sh`.
