# Edunex — AI-Powered Ethiopian Learning Platform

Full-stack LMS (PHP 8 + MySQL, dark UI) for admin, director, teacher, student,
parent and guest roles, with a C desktop client (ncurses + libcurl) that syncs
through the JSON API.

## Quick start (development)

1. Install the database and seed accounts:

   ```bash
   php database/install.php --demo --root-pass=<YOUR_MYSQL_ROOT_PASSWORD>
   # non-default server: add --host=127.0.0.1 --port=3306
   ```

2. Serve the app (dev server):

   ```bash
   php -S 127.0.0.1:8080 index.php
   ```

3. Open http://localhost:8080 — seeded logins use password `Passw0rd!`
   (`admin@edunex.local`, `director@edunex.local`, `teacher@edunex.local`,
   `student@edunex.local`, `parent@edunex.local`; student ID
   `AAIS-2026-000001` also works).

## AI models (Ollama)

The AI features need two Ollama models, registered as `edunex-tutor` (qwen2.5
3B, required) and `edunex-vision` (deepseek-vl2-tiny, optional). They are
multi-GB, so they are **not** committed to this repository — download them
with:

```bash
./download-models.sh
```

The script installs Ollama if missing, downloads the models, registers them
under the names Edunex expects, and prints the final `ollama list`.

## Configuration

`config/config.php` — DB credentials and security keys are read from environment
variables when set (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`), else
they fall back to the defaults. After installing, change `CSRF_SECRET`,
`ENCRYPTION_KEY` and `API_SECRET` to strong random values.

The AI layer (`models/`) uses a provider abstraction: `local` (offline,
rule-based, works with no API key) or `openai` (any OpenAI-compatible endpoint).
Configure it in Settings → AI & Learning (`ai_provider`, `ai_api_url`,
`ai_api_key`, `ai_model`). The PDF-book pipeline (`teacher/book`) extracts text
with `pdftotext` (falls back to `mutool`/`gs`) and generates a course + exam
through the configured provider.

Example running against a test MySQL on port 3307:

```bash
DB_PORT=3307 php -S 127.0.0.1:8080 index.php
```

## Test suite

`tests/e2e.sh` runs 125 checks (public pages, all role dashboards, forms, API
auth, JSON endpoints) against a running server and reports PASS/FAIL counts.
`tests/audit.sh` crawls every nav-reachable route per role and fails on
404/403/500 or placeholder pages.

```bash
bash tests/e2e.sh
bash tests/audit.sh
```

## C desktop client

```bash
cd desktop && make          # builds ./edunex-cli (ncurses + curl + json-c)
make install                # installs to /usr/local/bin
```

The client signs in via `api/login`, polls `api/notifications/poll`, and calls
`api/ai/chat` for the offline AI tutor. All API calls use the HMAC bearer token
issued at login.

## JSON API

Base: `index.php?r=api/<endpoint>`. Authenticate with
`Authorization: Bearer <token>` obtained from `api/login`
(`{"identifier": "...", "password": "..."}`).

Endpoints: login, notifications/poll, messages/send, ai/chat, attendance
(teacher mark + student code check-in), attendance-mobile, reactions, upload,
notify, settings, users, courses, calendar, library, gamification, transfers,
reports, backups, activity, search.

## Production (Apache)

1. Point a vhost at `public/` (see `deploy/edunex-vhost.conf`) with
   `AllowOverride All` — `public/.htaccess` rewrites all requests to `index.php`.
2. Ensure `storage/` and its subdirectories are writable by the web server.
3. Set `APP_ENV` to `production` in `config/config.php`.

## Modules

Courses/lessons, exams & grading, assignments, attendance (incl. QR + student
code), grades, AI tutor/assistant/flashcards/quiz + PDF-book course generator,
forum, library, certificates (issued on course completion), transfers
(school-to-school referral codes; the full student record — profile, XP,
badges, goals, certificates, grades, attendance — is copied to the new school),
gamification (XP/levels/challenges/leaderboard), messaging, announcements,
notifications, calendar, reports (CSV), backups, admin (schools, departments,
subjects, groups, users, roles, system logs), integrity ledger (a SHA-256
hash-chained, tamper-evident log of grading, attendance and certificate events,
visible at Admin → Integrity Ledger).

## Account model

- Students self-register; a homeroom teacher verifies them (pending for up to
  24h).
- The Super Admin creates Directors (one per school).
- Directors create teachers and approve/reject school transfers.
- Teachers create parents and link them to students, bulk-import users from
  Excel (`.xlsx`/`.csv`), and verify new student accounts.
- Students can be marked inactive — inactive students keep course/exam access
  (re-exam ready) but lose attendance/grades/schedule/leaderboard menus.
