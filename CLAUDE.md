# CLAUDE.md — ENC Roster Creator

Это руководство для AI-ассистентов, работающих с данным монорепо. Для правил, специфичных для Laravel, смотри `backend/CLAUDE.md`.

## Обзор архитектуры

Монорепо из двух независимых приложений за общим Nginx:

```
Браузер → Nginx :80
              ├── /api/* → PHP-FPM (Laravel, backend:9000)
              └── /*     → Vite dev-сервер (frontend:5173) | static dist (prod)
```

Аутентификация: **Sanctum API-токены** (не cookie-сессии). Токен хранится в `localStorage` под ключом `enc_token` и автоматически добавляется в заголовок `Authorization: Bearer` через `useApi` composable.

## Правила работы с кодом

### Общие

- Всегда запускай Pint перед финализацией PHP-изменений: `vendor/bin/pint --dirty --format agent`
- Не создавай документационные файлы, если пользователь явно не просит
- Проверяй соседние файлы перед созданием нового — возможно, паттерн уже есть

### Backend (Laravel 12 / PHP 8.4)

- **Маршруты**: только в `routes/api.php`. Нет web-маршрутов для API
- **Контроллеры**: тонкие — логику выносить в модели или сервисы
- **Валидация**: только через Form Request классы (`php artisan make:request`)
- **Модели**: всегда `$fillable`. Отношения с типами возврата. Eager loading для N+1
- **Форматирование**: snake_case для полей БД и JSON-ответов API
- **Миграции**: `php artisan make:migration` — никогда вручную
- **Тесты**: Pest 4 (`php artisan make:test --pest`)

Запрещено:
- `DB::` вместо Eloquent (кроме сложных запросов)
- `env()` вне config-файлов
- Inline-валидация в контроллерах

### Frontend (Vue 3 + TypeScript)

- **Все компоненты**: `<script setup lang="ts">` — без Options API
- **Типы**: определяются в `src/types/Api.ts`. Не дублировать локальные интерфейсы если тип уже есть
- **HTTP**: только через `useApi()` composable — он добавляет токен и обрабатывает ошибки
- **Стили**: Tailwind v4 классы в template. `@apply` в `<style scoped>` требует `@reference '../main.css'`
- **State**: Pinia stores в `src/stores/`. `userStore` — единственный источник истины для auth
- **Утилиты**: перед написанием новой функции проверь `src/utils/` и `src/composables/`

Запрещено:
- `defineProps(['propName'])` — только типизированная форма `defineProps<{ prop: Type }>()`
- `ref<Set<T>>()` для реактивных коллекций — использовать `reactive(new Set<T>())`
- Прямые `fetch()` вызовы в компонентах — только через `useApi()`
- Дублирование `suffix()`/`positionBadge()` — они уже в `src/utils/`

## Ключевые файлы

| Файл | Назначение |
|---|---|
| `frontend/src/composables/useApi.ts` | Fetch-обёртка: loading/error refs, AbortController, Bearer token |
| `frontend/src/stores/userStore.ts` | Auth state: token, user, setAuth/clearAuth/logout |
| `frontend/src/types/Api.ts` | Все TypeScript-интерфейсы API (Player, Country, RosterGroup…) |
| `frontend/src/utils/pluralRu.ts` | `playerSuffix(n)` — русские окончания |
| `frontend/src/utils/positionBadge.ts` | `positionBadge(pos)` → CSS-класс бейджа |
| `frontend/src/utils/resolvePhoto.ts` | HLTV silhouette → SVG placeholder |
| `backend/routes/api.php` | Все API-маршруты |
| `backend/app/Models/Player.php` | Модель игрока (country_id, nickname, photo_url…) |
| `backend/app/Models/CountryRoster.php` | Ростер страны (country_id + player_ids) |
| `docker/nginx/default.conf` | Dev nginx: /api → PHP-FPM, / → Vite |
| `docker/nginx/prod.conf` | Prod nginx: /api → PHP-FPM, / → static dist |
| `docker-compose.prod.yml` | Production-стек (нет Vite, нет открытых портов БД) |

## Схема БД

```
users              — id, name, email, password
personal_access_tokens — Sanctum токены
countries          — id, name, code (UNIQUE, ISO 3166-1 alpha-2)
players            — id, nickname (UNIQUE), name, surname, date_of_birth,
                     position, country_id (FK), photo_url
country_rosters    — id, country_id (UNIQUE), player_ids (JSON)
```

## Паттерны

### Добавить новый защищённый эндпоинт

```php
// routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/my-resource', [MyController::class, 'store']);
});
```

```ts
// Vue-компонент
const { apiFetch, loading, error } = useApi()
// useApi автоматически добавит Bearer token
await apiFetch('/api/my-resource', { method: 'POST', body: JSON.stringify(data) })
```

### Добавить новый тип в Api.ts

```ts
// src/types/Api.ts
export interface MyModel {
  id: number
  name: string
  // snake_case — как приходит из Laravel
}
```

### Новый Vue view

1. `frontend/src/views/MyView.vue` — `<script setup lang="ts">`
2. Добавить маршрут в `frontend/src/router/index.ts` (lazy import)
3. Добавить ссылку в `NavBar.vue` если нужно в навигации

## Окружение

```bash
# Dev
docker compose up -d --build

# Логи
docker compose logs -f backend
docker compose logs -f frontend

# Artisan
docker compose exec backend php artisan <command>

# Пересборка фронта (если HMR не подхватил)
docker compose restart frontend
```

## Известные особенности

- **Windows + Docker + HMR**: Vite использует polling (`usePolling: true` в vite.config.ts) — изменения подхватываются с задержкой ~1s
- **node_modules / vendor**: хранятся в анонимных Docker volumes — изолированы от хоста. После `npm install` / `composer install` на хосте нужно запускать команды внутри контейнера
- **@apply в scoped styles**: Tailwind v4 требует `@reference '../main.css'` в начале `<style scoped>` для доступа к переменным `@theme`
- **HLTV scraping**: сайт за Cloudflare — прямые HTTP-запросы возвращают 403. Используется FlareSolverr через Docker profile `scraper`
