# Task API 📋

A clean, production-ready **Task Management REST API** built with **Laravel 10** and **MySQL**, featuring a vanilla JS dashboard UI.

---

## Tech Stack

| Layer      | Technology                      |
|------------|---------------------------------|
| Framework  | Laravel 10 (PHP 8.1+)           |
| Database   | MySQL 8.0+                      |
| API Style  | RESTful JSON                    |
| Testing    | PHPUnit (SQLite in-memory)      |
| Frontend   | Vanilla JS (no framework)       |

---

## Project Structure

```
task-api/
├── app/
│   ├── Console/Kernel.php
│   ├── Enums/
│   │   ├── TaskPriority.php        # low | medium | high
│   │   └── TaskStatus.php          # pending | in_progress | done
│   ├── Exceptions/Handler.php
│   ├── Http/
│   │   ├── Controllers/
        
│   │   │   └──Controller.php
│   │   │   └── TaskController.php  # All 5 endpoints
│   │   ├── Kernel.php
│   │   ├── Middleware/             # Standard Laravel middleware
│   │   ├── Requests/
│   │   │   ├── StoreTaskRequest.php
│   │   │   └── UpdateTaskStatusRequest.php
│   │   └── Resources/
│   │       └── TaskResource.php    # Clean JSON output
│   ├── Models/Task.php             # Eloquent model + business logic
│   └── Providers/
├── bootstrap/app.php
├── config/
├── database/
│   ├── factories/TaskFactory.php
│   ├── migrations/
│   │   └── 2024_01_01_000000_create_tasks_table.php
│   └── seeders/DatabaseSeeder.php
├── public/
├── resources/views/welcome.blade.php  # Vanilla JS UI dashboard
├── routes/
│   ├── api.php                     # API routes
│   └── web.php
├── tests/Feature/TaskApiTest.php   # 13 feature tests
├── task_api_dump.sql               # Ready-to-import SQL dump
├── .env.example
├── artisan
└── composer.json
```

---

## Quick Start (Local)

### 1. Clone / Extract

```bash
unzip task-api.zip
cd task-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your MySQL credentials:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_api
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Create Database & Run Migrations

```bash
# Create the database
mysql -u root -p -e "CREATE DATABASE task_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations and seed
php artisan migrate:fresh --seed
```

### 5. Start the Server

```bash
php artisan serve
# Listening on http://127.0.0.1:8000
```

Open **http://127.0.0.1:8000** in your browser to see the dashboard.

### Database Commands

```bash
# Create tables and seed with sample data
php artisan migrate:fresh --seed

# Just run migrations
php artisan migrate

# Reset database (drop all tables)
php artisan migrate:reset

# Rollback last migration batch
php artisan migrate:rollback
```

---

## Running Tests

Tests run against an **in-memory SQLite** database — no MySQL connection needed.

```bash
php artisan test
# or
./vendor/bin/phpunit
```

All tests should pass ✅

---

## API Reference

Base URL: `http://localhost:8000/api`

All requests must include the header:
```
Accept: application/json
```

---

### 1. Create a Task

**POST** `/api/tasks`

**Request Body:**
```json
{
  "title":    "Fix login bug",
  "due_date": "2026-04-05",
  "priority": "high"
}
```

**Rules:**
- `title` + `due_date` combination must be unique
- `due_date` must be today or later
- `priority` must be `low`, `medium`, or `high`
- `status` is automatically set to `pending`

**201 Created:**
```json
{
  "message": "Task created successfully.",
  "data": {
    "id": 1,
    "title": "Fix login bug",
    "due_date": "2026-04-05",
    "priority": "high",
    "status": "pending",
    "created_at": "2026-04-01T08:00:00+00:00",
    "updated_at": "2026-04-01T08:00:00+00:00"
  }
}
```

**422 Unprocessable:**
```json
{
  "message": "The title has already been taken.",
  "errors": {
    "title": ["A task with this title already exists for the given due date."]
  }
}
```

---

### 2. List Tasks

**GET** `/api/tasks`

Optional query param: `?status=pending|in_progress|done`

Tasks are sorted: **priority high → medium → low**, then **due_date ascending**.

**200 OK (with tasks):**
```json
{
  "message": "Tasks retrieved successfully.",
  "total": 3,
  "data": [
    { "id": 1, "title": "...", "priority": "high",   "status": "pending",     "due_date": "2026-04-02" },
    { "id": 2, "title": "...", "priority": "medium",  "status": "in_progress", "due_date": "2026-04-03" },
    { "id": 3, "title": "...", "priority": "low",     "status": "pending",     "due_date": "2026-04-10" }
  ]
}
```

**200 OK (empty):**
```json
{
  "message": "No tasks found.",
  "data": []
}
```

---

### 3. Update Task Status

**PATCH** `/api/tasks/{id}/status`

**Request Body:**
```json
{ "status": "in_progress" }
```

**Flow:** `pending` → `in_progress` → `done`

No skipping. No reverting.

**200 OK:**
```json
{
  "message": "Task status updated to 'in_progress'.",
  "data": { "id": 1, "status": "in_progress", ... }
}
```

**422 — Invalid transition:**
```json
{
  "message": "Invalid status transition. Current status is 'pending'. Next allowed status is 'in_progress'."
}
```

---

### 4. Delete a Task

**DELETE** `/api/tasks/{id}`

Only tasks with `status = done` can be deleted.

**200 OK:**
```json
{ "message": "Task deleted successfully." }
```

**403 Forbidden:**
```json
{
  "message": "Only tasks with status 'done' can be deleted. This task is currently 'pending'."
}
```

---

### 5. Daily Report (Bonus)

**GET** `/api/tasks/report?date=YYYY-MM-DD`

**200 OK:**
```json
{
  "date": "2026-04-01",
  "summary": {
    "high":   { "pending": 2, "in_progress": 1, "done": 1 },
    "medium": { "pending": 1, "in_progress": 0, "done": 1 },
    "low":    { "pending": 1, "in_progress": 0, "done": 0 }
  }
}
```

---

## Deployment on Railway

[Railway](https://railway.app) is the easiest free MySQL + PHP host.

### Steps

1. **Push to GitHub**
```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/task-api.git
git push -u origin main
```

2. **Create a Railway project**
   - Go to https://railway.app → New Project → Deploy from GitHub
   - Select your `task-api` repository

3. **Add a MySQL plugin**
   - In your Railway project → New → Database → MySQL
   - Railway auto-injects `MYSQL_*` env vars

4. **Set environment variables** in Railway dashboard:
```
APP_NAME=Task API
APP_ENV=production
APP_KEY=          ← run: php artisan key:generate --show
APP_DEBUG=false
APP_URL=https://YOUR-APP.railway.app

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}
```

5. **Add a build command** in Railway settings:
```bash
composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan db:seed --force
```

6. **Start command:**
```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

---

## Deployment on Render

1. Go to https://render.com → New Web Service → Connect GitHub repo
2. Add a **MySQL** (or PostgreSQL) database service
3. Set the same env vars as above
4. Build command: `composer install --no-dev`
5. Start command: `php artisan serve --host 0.0.0.0 --port $PORT`

---

## Example cURL Requests

```bash
# Create a task
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title":"Fix login bug","due_date":"2026-04-05","priority":"high"}'

# List all tasks
curl http://localhost:8000/api/tasks \
  -H "Accept: application/json"

# Filter by status
curl "http://localhost:8000/api/tasks?status=pending" \
  -H "Accept: application/json"

# Advance status to in_progress
curl -X PATCH http://localhost:8000/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status":"in_progress"}'

# Advance status to done
curl -X PATCH http://localhost:8000/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status":"done"}'

# Delete a done task
curl -X DELETE http://localhost:8000/api/tasks/1 \
  -H "Accept: application/json"

# Daily report
curl "http://localhost:8000/api/tasks/report?date=2026-04-01" \
  -H "Accept: application/json"
```

---

## Business Rule Summary

| Rule                                          | Endpoint             | HTTP Code |
|-----------------------------------------------|----------------------|-----------|
| Title unique per due_date                     | POST /tasks          | 422       |
| due_date must be today or future              | POST /tasks          | 422       |
| Status starts as `pending`                    | POST /tasks          | —         |
| Sort: high → medium → low, then due_date ASC  | GET /tasks           | —         |
| Status can only advance one step forward      | PATCH /status        | 422       |
| Cannot skip or revert status                  | PATCH /status        | 422       |
| Only `done` tasks can be deleted              | DELETE /tasks/{id}   | 403       |
| Report shows all priority×status combos       | GET /tasks/report    | 200       |

---

## Database

- **Database used:** MySQL 8.0+
- **Dump file:** `task_api_dump.sql` (included in root)
- **Unique index:** `(title, due_date)` — enforced at both DB and application level
- **Indexes:** `status`, `due_date`, `priority` — for fast filtering and reporting

---

*Built by a developer who likes clean code, proper validations, and databases that don't lie.*
