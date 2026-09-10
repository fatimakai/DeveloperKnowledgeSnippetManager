# PromptForge

PromptForge is a collaborative library for creating, discovering, and refining prompts used with large language models. It is built with Laravel 12 and Livewire 3.

## Current features

- Public prompt discovery and private personal prompts
- Prompt text, target model, description, example input, and example output
- Tags, full prompt search, filters, and popularity/recent sorting
- Immutable version history with field-by-field comparison and safe restore
- Queued OpenRouter prompt analysis with intent, weaknesses, and an improved before/after prompt
- Community upvotes and personal bookmarks
- Secure owner-only editing and deletion
- Individual and account-wide JSON exports
- Sanctum-authenticated Prompt API and public read-only API
- User profiles, email verification, and queued welcome email
- Responsive light/dark interface

Shared collections, OAuth, 2FA, RBAC, and sandbox subscriptions are planned in later phases.

## Stack

- PHP 8.2+ and Laravel 12
- Livewire 3, Blade, Tailwind CSS, Alpine.js, and Vite
- MySQL for production; SQLite in memory for tests
- Sanctum for API tokens
- Redis-compatible queues and cache
- Laravel Octane/Swoole-ready Docker setup

## Local setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
npm run build
php artisan serve
```

Configure database, mail, cache, and queue values in `.env`. Run a queue worker when using an asynchronous queue connection:

```bash
php artisan queue:work
```

To enable AI analysis, set `OPENROUTER_API_KEY` and optionally `OPENROUTER_MODEL`. `PROMPT_ANALYSIS_PER_HOUR` controls the per-user cost limit and defaults to five.

## Tests

```bash
composer test
npm run build
```

The test environment uses an in-memory SQLite database and does not require Docker or MySQL.

## Web routes

- `/` - landing page
- `/prompts` - public prompt discovery
- `/prompts/{slug}` - authorized prompt detail
- `/prompts/{slug}/history` - owner-only version timeline and comparison
- `/dashboard` - rankings
- `/prompts/mine` - personal library
- `/prompts/bookmarked` - bookmarks
- `/prompts/create` - prompt creation

## API routes

Authentication uses bearer tokens returned by `POST /api/login`.

- `GET /api/public/prompts`
- `GET /api/public/prompts/{slug}`
- `GET /api/prompts`
- `POST /api/prompts`
- `GET /api/prompts/{slug}`
- `PUT/PATCH /api/prompts/{slug}`
- `DELETE /api/prompts/{slug}`
- `GET /api/prompts/{slug}/analyses`
- `POST /api/prompts/{slug}/analyses`
- `GET /api/prompts/{slug}/analyses/{analysis}`
- `POST /api/logout`

Authenticated API endpoints always enforce prompt policies. Public endpoints only return prompts explicitly marked public.
