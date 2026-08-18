# Edunex — AI-Powered Ethiopian Learning Platform

## Overview

Edunex is a full-stack **Learning Management System (LMS)** built for Ethiopian
schools: a PHP 8 + MySQL web application with a dark, role-based UI and a C
desktop client (ncurses + libcurl) that syncs through the same JSON API. It
covers the full school lifecycle — administration, teaching, exams, attendance,
messaging, transfers, gamification and an AI tutoring layer — with an
integrity ledger that makes grading and certificates tamper-evident.

## Problem

Ethiopian schools juggle paper registers, spreadsheets and disconnected
tools. Teachers cannot easily generate courses and exams, students move
between schools with no portable record, attendance is hard to audit, and AI
assistance requires cloud APIs that offline or low-bandwidth campuses cannot
reach.

## Solution

Edunex puts the whole school on one platform with clear role separation
(super admin, director, teacher, student, parent, guest):

- **Offline-first AI** — a local Ollama tutor (`edunex-tutor`,
  qwen2.5 3B) with an optional vision model; a provider abstraction (`local` /
  `openai`) means the AI layer works with no API key at all.
- **PDF-book course generator** — teachers upload a book and get a course plus
  an exam through the configured AI provider.
- **Portable student records** — school-to-school transfers move the complete
  profile (XP, badges, goals, certificates, grades, attendance) via referral
  codes.
- **Tamper-evident integrity ledger** — grading, attendance and certificate
  events are SHA-256 hash-chained and visible at Admin → Integrity Ledger.
- **C desktop client** — ncurses terminal client for low-resource
  environments, syncing via the JSON API with HMAC bearer auth.

## Features

- Courses, lessons, exams & grading, assignments
- Attendance: teacher marking, QR codes, student code check-in,
  attendance-mobile endpoints
- AI tutor, AI flashcards, AI quiz, PDF-book course generator
- Gamification: XP, levels, challenges, leaderboard
- Messaging, announcements, notifications, calendar
- Library and forum
- Certificates issued on course completion
- School-to-school transfers with referral codes
- Excel (`.xlsx`/`.csv`) bulk user import
- Reports (CSV) and backups
- Admin: schools, departments, subjects, groups, users, roles, system logs
- Integrity ledger (SHA-256 hash-chained grading/attendance/certificates)
- JSON API + C desktop client (ncurses)

## Architecture

```
                 E D U N E X
                      │
      ┌───────────────┼─────────────────┐
      ▼               ▼                 ▼
  PHP 8 + MySQL   C desktop client   Ollama (AI)
  (web + API)     (ncurses, libcurl)  tutor/vision
                      │
                  HMAC bearer + JSON API
```

```
├── app/            PHP application (modules, controllers, models)
├── config/         Configuration
├── database/       Installer (php database/install.php)
├── deploy/         Apache vhost template
├── desktop/        C desktop client (ncurses + libcurl)
├── docs/           Documentation
├── models/         AI provider abstraction (local / openai)
├── public/         Web root
├── storage/        Storage
└── tests/          e2e.sh (125 checks) + audit.sh (route crawler)
```

## Technology

| Layer | Technology |
|---|---|
| Backend | PHP 8, MySQL |
| Frontend | HTML, CSS, Pure JavaScript (dark UI) |
| Desktop | C (ncurses, libcurl, json-c) |
| AI | Ollama — `edunex-tutor` (qwen2.5 3B, required), `edunex-vision` (deepseek-vl2-tiny, optional) |

## Installation

Requirements: PHP 8, MySQL, and (for AI) Ollama.

```bash
# 1. Install the database and seed accounts
php database/install.php --demo --root-pass=<YOUR_MYSQL_ROOT_PASSWORD>
# non-default server: add --host=127.0.0.1 --port=3306

# 2. Serve the app (dev server)
php -S 127.0.0.1:8080 index.php

# 3. (Optional) AI models — multi-GB, not committed to the repo
./download-models.sh     # installs Ollama if missing, pulls + registers models
```

## Usage

Open http://localhost:8080 — seeded logins use password `Passw0rd!`:
`admin@edunex.local`, `director@edunex.local`, `teacher@edunex.local`,
`student@edunex.local`, `parent@edunex.local` (student ID `AAIS-2026-000001`
also works).

- **Students** self-register; a homeroom teacher verifies them (pending up to
  24h).
- **Super Admin** creates Directors (one per school); **Directors** create
  teachers and approve transfers; **Teachers** create parents, bulk-import
  from Excel, verify students.
- **AI** is configured at Settings → AI & Learning (`ai_provider`,
  `ai_api_url`, `ai_api_key`, `ai_model`); the PDF-book pipeline uses
  `pdftotext` (falls back to `mutool`/`gs`).
- **Desktop client**: `cd desktop && make` → `./edunex-cli`; it logs in via
  `api/login`, polls `api/notifications/poll` and talks to the offline AI
  tutor.

**JSON API** — base `index.php?r=api/<endpoint>`, authenticate with
`Authorization: Bearer <token>` from `api/login`. Endpoints: login, polls,
messages, ai/chat, attendance, reactions, uploads, settings, users, courses,
calendar, library, gamification, transfers, reports, backups, activity,
search.

**Tests:**

```bash
bash tests/e2e.sh      # 125 checks against a running server
bash tests/audit.sh    # crawls every nav route per role, fails on 404/403/500
```

## Security

- **HMAC bearer tokens** — all API calls (web and desktop client) are
  authenticated with tokens issued at login.
- **CSRF protection** — `CSRF_SECRET` guards state-changing requests; change
  it plus `ENCRYPTION_KEY` and `API_SECRET` after install.
- **Integrity ledger** — grading, attendance and certificate events are
  SHA-256 hash-chained and tamper-evident.
- **Role separation** — admin/director/teacher/student/parent/guest each see
  only their permitted surfaces.
- **Account lifecycle** — pending student verification, inactive students
  keep exam access but lose grading/attendance menus.

## Screenshots

Screenshots will be added here as the interface is finalized (dark dashboard,
teacher course builder, AI tutor chat, integrity ledger).

## Roadmap

- Browser-driven E2E smoke tests for newer modules (AI, transfers, chat)
- Expanded AI: automated paper-to-exam pipelines and voice responses
- Mobile attendance app
- Arabic/Amharic localization pass
- Production hardening: rate limiting on AI endpoints, audit export

## License

ARWE Public Source License (ARWE-PSL) v1.0 — see [LICENSE](LICENSE) and [NOTICE](NOTICE).

Copyright © 2026 Henok Akriso. All rights reserved. Developer / Project Alias: Sergio — Founder of Halziz. "Edunex" and "ARWE" are trademarks of the ARWE project; see the license for trademark terms.