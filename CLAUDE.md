# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**SQL Playground** is a Laravel 12 web application for learning SQL through interactive query exercises. Users can write and execute SQL queries against a PostgreSQL database in a controlled, sandboxed environment with built-in security restrictions.

## Architecture

### Core Components

- **TicketController** (`app/Http/Controllers/TicketController.php`): Manages SQL ticket execution. Validates queries for security, enforces read-only operations (SELECT, WITH, EXPLAIN), handles parameter binding, and applies safety limits.
- **Authentication**: Laravel Breeze for user management (register, login, email verification, password reset).
- **Database**: PostgreSQL with session/queue/cache storage via database driver.
- **Frontend**: Blade templates + Vite + Tailwind CSS + Alpine.js.

### Request Flow

1. User visits `/tickets/{number}` → TicketController::show loads ticket Blade view
2. User executes query → AJAX call to `/api/tickets/{number}/execute` (TicketController::execute)
3. Controller validates SQL security, parameter bindings, and statement type
4. Executes query with safety limit (LIMIT 1000 for SELECT/WITH), returns JSON with columns, rows, execution time

### SQL Security Model

Forbidden patterns (regex): semicolons, DROP/DELETE/INSERT/UPDATE/TRUNCATE/ALTER TABLE, GRANT/REVOKE, EXECUTE/CALL.

Allowed statements: SELECT, WITH/RECURSIVE, EXPLAIN, CREATE/DROP INDEX.

Parameter binding: Named placeholders (e.g., `:user_id`) extracted from query and resolved from query string.

## Development

### Setup

```bash
composer run setup
```

This installs PHP and Node dependencies, generates `.env`, creates database key, runs migrations, and builds frontend assets.

### Development Server

```bash
composer run dev
```

Runs concurrent processes: PHP server (8000), queue listener, logs via pail, and Vite dev server.

### Frontend Development

```bash
npm run dev       # Vite dev server with hot reload
npm run build     # Production build
```

### Creating Tickets

#### Via CLI
```bash
php artisan ticket:create <numero> "<titulo>"
```

Example:
```bash
php artisan ticket:create 5 "Consulta de vendedores"
```

#### Via UI (Modal)
Click the **"Nuevo Ticket"** button in the navbar (right of the profile dropdown). 
- Number is auto-filled with the next available number
- Enter a ticket title and click "Crear"

Both methods create:
- `resources/views/tickets/ticket-{N}.blade.php` — UI component
- `database/queries/ticket-{N}.sql` — SQL query template
- Automatically added to navbar

#### Implementation Details
- **Service**: `App\Services\TicketService` — Shared logic for creating tickets and getting next number
- **Command**: `App\Console\Commands\TicketCreate` — CLI command wrapper
- **API Endpoint**: `POST /api/tickets/create` — Handles form submissions
- **Component**: `resources/views/components/create-ticket-modal.blade.php` — Modal UI

### Editing Query Files

Each ticket view includes an inline SQL query editor. No need to edit files manually.

**How it works:**
1. Click **"✎ Editar"** button on any ticket page
2. Modify the SQL query in the textarea
3. Click **"✓ Guardar"** to save
4. Changes are written directly to `database/queries/ticket-{N}.sql`

**Implementation:**
- **Component**: `resources/views/components/query-editor.blade.php` — Inline editor UI
- **API Endpoint**: `POST /api/tickets/update-query` — Saves query to file
- **Method**: `TicketController::updateQuery()` — Handles file write

### Testing

```bash
composer run test
```

Runs PHPUnit with in-memory SQLite database. Tests use:
- `tests/Unit/` for unit tests
- `tests/Feature/` for integration tests

### Code Formatting

```bash
./vendor/bin/pint          # Check formatting
./vendor/bin/pint --fix    # Auto-fix formatting
```

### Running Individual Tests

```bash
php artisan test tests/Feature/TicketTest.php
php artisan test tests/Feature/TicketTest.php --filter=testMethodName
```

## File Structure

- `app/Http/Controllers/` — Route handlers
- `app/Models/` — Eloquent models
- `resources/views/` — Blade templates
  - `layouts/` — App layout components
  - `auth/` — Authentication templates
  - `tickets/` — Ticket view templates (e.g., `ticket-1.blade.php`)
- `routes/web.php` — Route definitions
- `routes/auth.php` — Authentication routes
- `database/migrations/` — Database schema
- `database/queries/` — SQL query files (e.g., `ticket-1.sql`, `ticket-2.sql`)
- `database/seeders/` — Database seeders
- `database/factories/` — Eloquent factories for tests
- `config/` — Application configuration
- `public/` — Public assets (compiled via Vite)
- `storage/` — Logs, cache, sessions
- `tests/` — Test files

## Key Implementation Details

### TicketController Methods

- `show(int $ticketNumber)` — Renders ticket view if it exists; queries at `/tickets/{number}`
- `execute(int $ticketNumber)` — Executes SQL from `database/queries/ticket-{number}.sql`; API endpoint at `/api/tickets/{number}/execute`

### Private Methods

- `validateSqlSecurity(string $sql)` — Checks forbidden patterns
- `startsWithAllowedStatement(string $sql)` — Ensures query begins with allowed keyword
- `isResultSetQuery(string $sql)` — Distinguishes SELECT/WITH/EXPLAIN from index operations
- `applySafetyLimitIfNeeded(string $sql)` — Wraps SELECT/WITH without LIMIT in subquery with LIMIT 1000
- `resolveBindings(string $sql)` — Extracts `:placeholder` names from SQL and resolves from query string (e.g., `?user_id=123`)
- `stripLeadingComments(string $sql)` — Removes `--` and `/* */` comments for parsing

### Ticket Structure

Each ticket has two files:

1. **View**: `resources/views/tickets/ticket-{N}.blade.php` — Display instructions and UI
2. **Query**: `database/queries/ticket-{N}.sql` — SQL file executed when user clicks "Execute"

The view file uses Alpine.js to fetch and display results from the API.

## Database

- **Driver**: PostgreSQL (configured in `.env` as `DB_CONNECTION=pgsql`)
- **Schema**: Defined in `database/lbd_schema_reducido.sql` (reduced schema for practice)
- **Seeding**: `database/seed-db.sql` for populating test data
- **Test DB**: In-memory SQLite for PHPUnit (see `phpunit.xml`)

## Frontend Stack

- **Vite**: Build tool with hot reload
- **Tailwind CSS**: Utility-first CSS framework with forms plugin
- **Alpine.js**: Lightweight JavaScript interactivity
- **Blade**: Laravel's templating engine

## Environment Variables

Key variables in `.env.example`:

- `APP_NAME` — Displayed in Vite Vite
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` — PostgreSQL config
- `SESSION_DRIVER=database` — Store sessions in DB
- `QUEUE_CONNECTION=database` — Queue storage
- `CACHE_STORE=database` — Cache storage

## RTK (Rust Token Killer)

Follow token optimization instructions from your global `.claude/CLAUDE.md`. Always prefix common commands with `rtk`:

```bash
rtk composer run test           # Failures only
rtk npm run build               # Build output
rtk git status                  # Compact status
rtk ./vendor/bin/pint --fix    # Compact output
```
