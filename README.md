# ENC Roster Creator

Веб-приложение для формирования составов команд **European Nations Cup** (CS2). Позволяет просматривать игроков, создавать ростеры по странам и управлять ими.

## Стек технологий

| Слой | Технологии |
|---|---|
| **Backend** | Laravel 12, PHP 8.4, Sanctum (API-токены) |
| **Frontend** | Vue 3 + TypeScript, Vite 7, Tailwind CSS v4, Pinia |
| **База данных** | MySQL 8.0 |
| **Кеш / очереди** | Redis 7 |
| **Веб-сервер** | Nginx 1.27 (reverse proxy) |
| **Окружение** | Docker Compose |
| **Линтинг** | Laravel Pint (PHP), ESLint + Oxlint (TS/Vue) |
| **Тестирование** | Pest 4 |

## Быстрый старт

### Зависимости

- [Docker Desktop](https://www.docker.com/products/docker-desktop/)
- Git

### Запуск

```bash
git clone https://github.com/genjy1/enc-roster-creator.git
cd enc-roster-creator

docker compose up -d --build
```

Приложение будет доступно по адресу **http://localhost**

> При первом запуске `docker-entrypoint.sh` автоматически генерирует `APP_KEY` и запускает миграции.

### Наполнение БД тестовыми данными

```bash
docker compose exec backend php artisan migrate:fresh --seed
```

## Структура проекта

```
enc-roster-creator/
├── backend/            # Laravel приложение
│   ├── app/
│   │   ├── Console/Commands/   # Artisan-команды (импорт игроков)
│   │   ├── Http/Controllers/   # AuthController, PlayerController, CountryRosterController
│   │   ├── Http/Requests/      # Form Request валидация
│   │   └── Models/             # User, Country, Player, CountryRoster
│   ├── database/
│   │   ├── migrations/
│   │   ├── factories/
│   │   └── seeders/
│   └── routes/api.php
├── frontend/           # Vue 3 SPA
│   └── src/
│       ├── components/     # AppSelect, PlayerCard, AuthModal, NavBar, AppPreloader
│       ├── composables/    # useApi (fetch + AbortController + Bearer token)
│       ├── stores/         # userStore (Pinia, токен в localStorage)
│       ├── types/          # Api.ts — типы моделей
│       ├── utils/          # pluralRu, positionBadge, resolvePhoto
│       └── views/          # MainPage, PlayersList, CountriesList, RosterCreate, RostersList
├── docker/nginx/       # Конфигурации nginx (dev + prod)
├── scraper/            # Node.js скрапер HLTV (FlareSolverr)
├── docker-compose.yml      # Dev-окружение
└── docker-compose.prod.yml # Production-окружение
```

## API

Все эндпоинты доступны по префиксу `/api`.

### Публичные

| Метод | Путь | Описание |
|---|---|---|
| `POST` | `/auth/register` | Регистрация (возвращает токен) |
| `POST` | `/auth/login` | Вход (возвращает токен) |
| `GET` | `/countries` | Список стран с игроками |
| `GET` | `/players` | Список всех игроков |
| `GET` | `/rosters` | Список ростеров |
| `GET` | `/roster/{countryId}` | Ростер конкретной страны |

### Защищённые (Bearer token)

| Метод | Путь | Описание |
|---|---|---|
| `POST` | `/auth/logout` | Выход (отзыв токена) |
| `GET` | `/auth/me` | Данные текущего пользователя |
| `POST` | `/roster` | Создать / обновить ростер |
| `DELETE` | `/roster/{countryId}/player/{playerId}` | Убрать игрока из ростера |

## Полезные команды

```bash
# Просмотр логов
docker compose logs -f backend
docker compose logs -f frontend

# Artisan
docker compose exec backend php artisan migrate:fresh --seed
docker compose exec backend php artisan route:list
docker compose exec backend php artisan tinker

# Форматирование PHP (Pint)
docker compose exec backend vendor/bin/pint

# Импорт игроков из JSON (после запуска скрапера)
docker compose exec backend php artisan import:players-json

# Скрапер HLTV (требует FlareSolverr)
docker compose --profile scraper up scraper
```

## Production-деплой (Coolify / любой VPS)

```bash
# Скопируй .env.prod.example → .env.prod и заполни секреты
cp .env.prod.example .env.prod

# Запуск production-стека
docker compose -f docker-compose.prod.yml --env-file .env.prod up -d --build
```

Production-стек собирает Vue-приложение внутри Docker (multi-stage build), убирает порты MySQL/Redis наружу и запускает `config:cache` / `route:cache` / `view:cache` при старте.

## Скрапер данных HLTV

Игровые данные (никнеймы, фото, страны) импортируются с [hltv.org/players](https://www.hltv.org/players).

```bash
# Запустить FlareSolverr + скрапер
docker compose --profile scraper up --build scraper

# Дождаться завершения, затем импортировать в БД
docker compose exec backend php artisan import:players-json
```

> HLTV защищён Cloudflare — скрапер использует [FlareSolverr](https://github.com/FlareSolverr/FlareSolverr) для обхода.
