

# RoleGenie — Technical Documentation

**ICS 499 Capstone Project**
Team: O'Shae Berteaux & Sovann Phay

---

## 1. What RoleGenie Is

RoleGenie is a web application that helps job seekers find relevant openings and prepare tailored application
materials with AI assistance. A user creates an account, uploads a PDF resume, searches live job listings pulled
from LinkedIn/Indeed/Glassdoor/Monster (via the JSearch aggregator API), and — for any listing — generates a
resume variant and a cover letter customized to that specific job using Anthropic's Claude API. Results are
tracked in a personal dashboard.

The product pitch (from the marketing landing page): *"Your wish for the perfect role — granted."* Upload a
resume or connect LinkedIn, and AI agents scan thousands of jobs, score them against your profile, and surface
only what truly fits.

> **Note on scope:** The application calls the JSearch REST API
> for listings and the Claude Messages API for text generation (resume/cover-letter drafting). There is no
> autonomous browsing agent, no auto-submission to employers, and no reminder emails yet. Section 12 lays out
> exactly what's built versus what's planned.

---

## 2. Tech Stack

| Layer | Technology |
|---|---|
| Language / runtime | PHP 8.x (procedural, no framework) |
| Database | MySQL 8.0 (accessed via PDO) |
| Frontend | Server-rendered PHP templates + Bootstrap 5 + jQuery |
| Fonts | Google Fonts (Playfair Display, Inter) |
| PDF parsing | `smalot/pdfparser` (Composer package), with a hand-rolled regex fallback |
| Job data | [JSearch API](https://rapidapi.com/) (RapidAPI), which aggregates LinkedIn/Indeed/Glassdoor/etc. |
| AI generation | Anthropic Claude API (`claude-haiku-4-5`) via direct cURL calls to `api.anthropic.com` |
| Dependency management | Composer (`composer.json` / `vendor/`) |
| Hosting target | Bluehost shared/VPS hosting, PHP 8.2+, MySQL 8.0+, Let's Encrypt SSL |
| Auth | Native PHP sessions (`$_SESSION`), `password_hash()` / `password_verify()` (bcrypt) |

There is no JavaScript build step, no SPA framework, and no ORM — this is intentionally a simple, classic
server-rendered PHP app suited to shared hosting.

---

## 3. Architecture

```
 Browser (Bootstrap/jQuery pages)
        │
        │  HTML form posts + fetch/AJAX (JSON)
        ▼
 ┌───────────────────────────────────────────────────────────┐
 │                    PHP application (RG/)                  │
 │                                                             │
 │  Pages (session-gated, render HTML):                       │
 │   index.php · register.php · login.php · logout.php ·      │
 │   dashboard.php · jobs.php                                 │
 │                                                             │
 │  JSON API endpoints (api/):                                 │
 │   search_jobs.php · upload_resume.php ·                    │
 │   generate_resume.php · generate_cover_letter.php           │
 │                                                             │
 │  Config (config/):                                          │
 │   env.php   (loads .env into getenv())                     │
 │   db.php    (PDO connection to MySQL)                      │
 └───────────────────────────────────────────────────────────┘
        │                          │                    │
        ▼                          ▼                    ▼
 ┌─────────────┐         ┌──────────────────┐   ┌──────────────────┐
 │   MySQL     │         │   JSearch API    │   │   Claude API     │
 │ role_genie  │         │  (RapidAPI, job  │   │ (Anthropic, text │
 │  database   │         │   listings)      │   │  generation)     │
 └─────────────┘         └──────────────────┘   └──────────────────┘
```

Every state-changing request re-checks `$_SESSION['user_id']`; there is no separate auth/session service — PHP's
built-in session handling is used directly on every page and API script.

---

## 4. Project Structure

```
RG/
├── index.php                 Public marketing/landing page
├── register.php               Sign-up form + account creation
├── login.php                  Login form + session creation
├── logout.php                 Destroys session, redirects to login
├── dashboard.php               Logged-in home: matched jobs + activity stats
├── jobs.php                    Resume upload + job search + AI generation UI
│
├── api/                        JSON endpoints called via jQuery AJAX
│   ├── search_jobs.php          GET  — queries JSearch, stores results, returns JSON
│   ├── upload_resume.php        POST — stores PDF, extracts text, saves to DB
│   ├── generate_resume.php      POST — Claude-tailored resume for one job
│   └── generate_cover_letter.php POST — Claude-generated cover letter for one job
│
├── config/
│   ├── env.php                 Minimal .env file loader (populates getenv/$_ENV/$_SERVER)
│   └── db.php                  PDO connection setup (throws/dies on failure)
│
├── assets/
│   ├── css/                    style.css, login.css, register.css, dashboard.css
│   └── js/app.js                Landing-page interactions (nav, smooth scroll, redirects)
│
├── uploads/resumes/             Uploaded PDF resumes (per-user, timestamped filenames)
│
├── RoleGenie/RoleGenie schema.sql   Canonical MySQL schema (5 tables)
│
├── vendor/                      Composer dependencies (smalot/pdfparser, symfony/polyfill-mbstring)
├── composer.json / composer.lock
│
├── .env / .env.example          Runtime secrets & config (DB creds, API keys)
├── .htaccess                    Denies web access to dotfiles (.env, .git)
│
├── Docs/
│   ├── requirements.md           Full functional/non-functional requirements spec
│   └── development_timeline.md   Sprint-by-sprint team plan
│
└── Mockup Pages/                 Early UI mockup screenshots
```

---

## 5. Database Schema

Database: `role_genie` (MySQL, `utf8mb4`). Defined in `RoleGenie/RoleGenie schema.sql`. Five tables:

### `users`
Core account record.
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| email | VARCHAR(255) | unique in practice (checked in app logic) |
| password_hash | VARCHAR(255) | bcrypt via `password_hash()` |
| full_name | VARCHAR(255) | |
| phone | VARCHAR(30) | |
| created_at / updated_at | DATETIME | auto-managed |

### `user_profiles`
One-to-one with `users`. Holds links to external job-site profiles (not yet used by any feature in the current
build — reserved for future LinkedIn/Indeed/Glassdoor/Monster integration). Auto-created (empty) at registration.
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| user_id | INT UNSIGNED FK → users.id | `ON DELETE CASCADE` |
| linkedin_url / indeed_url / glassdoor_url / monster_url | VARCHAR(512) | nullable |
| updated_at | DATETIME | |

### `resumes`
One row per uploaded resume version; only one `is_active = 1` per user at a time.
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| user_id | INT UNSIGNED FK → users.id | `ON DELETE CASCADE` |
| filename | VARCHAR(255) | original uploaded filename |
| file_path | VARCHAR(512) | relative path under `uploads/resumes/` |
| file_type | ENUM('pdf') | only PDF supported |
| parsed_text | LONGTEXT | extracted resume text, fed to Claude prompts |
| is_active | TINYINT(1) | most recent upload = 1, prior ones flipped to 0 |
| uploaded_at | DATETIME | |

### `job_listings`
Search results returned by JSearch, cached per user.
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| user_id | INT UNSIGNED FK → users.id | `ON DELETE CASCADE` |
| external_id | VARCHAR(255) | JSearch's `job_id` |
| source_platform | VARCHAR(100) | publisher (e.g. LinkedIn, Indeed) |
| title / company / location / url | VARCHAR | |
| salary_raw | VARCHAR(255) | not currently populated by `search_jobs.php` |
| employment_type | VARCHAR(100) | |
| is_remote | TINYINT(1) | |
| description | TEXT | truncated to 1000 chars on insert |
| fetched_at | DATETIME | used for `ON DUPLICATE KEY UPDATE` refresh |

### `applications`
One row per Claude-generated document (resume variant or cover letter) tied to a listing. Despite the name, a
row does **not** mean "submitted to the employer" in the current build — it means a document was generated for
that job (see Section 12).
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED PK | |
| user_id | INT UNSIGNED FK → users.id | `ON DELETE CASCADE` |
| listing_id | INT UNSIGNED FK → job_listings.id | `ON DELETE CASCADE` |
| cover_letter | LONGTEXT | nullable |
| resume_variant | LONGTEXT | nullable |
| applied_at / created_at | DATETIME | |

### Entity relationships
```
users 1─┬─1 user_profiles
        ├─* resumes
        ├─* job_listings ──* applications
        └───────────────────────┘  (also user_id on applications)
```

---

## 6. Authentication & Session Management

- **Registration** (`register.php`): validates name/email/password (min 8 chars, must match confirm field),
  checks for existing email, hashes the password with `password_hash(..., PASSWORD_BCRYPT)`, inserts into
  `users`, auto-creates a blank `user_profiles` row, then logs the user in immediately by setting
  `$_SESSION['user_id']` and `$_SESSION['user_name']`.
- **Login** (`login.php`): looks up the user by email, verifies the password with `password_verify()`, and on
  success sets the same session keys before redirecting to `dashboard.php`.
- **Logout** (`logout.php`): calls `session_destroy()` and redirects to `login.php`.
- **Route protection**: `dashboard.php`, `jobs.php`, and every `api/*.php` script check
  `isset($_SESSION['user_id'])` at the top and redirect (pages) or return HTTP 401 JSON (APIs) if absent.
- **No CSRF tokens or password-reset flow are implemented yet**, despite being called out in
  `requirements.md` (NFR-03, FR-01) — see Section 12.
- Social login buttons (Google/LinkedIn) on the login/register pages are non-functional placeholders
  (`alert('Google OAuth goes here.')`).

---

## 7. Core Features (Walkthrough)

### 7.1 Resume upload & parsing — `api/upload_resume.php`
1. Client-side (`jobs.php`) restricts to `.pdf`, max 5 MB, with a drag-and-drop zone.
2. Server re-validates: extension must be `.pdf`, and the first 4 bytes of the file must literally be `%PDF`
   (defends against a renamed non-PDF file).
3. File is saved to `uploads/resumes/` with a collision-resistant name:
   `{user_id}_{unix_timestamp}_{sanitized_original_name}`.
4. Text is extracted via `extractPdfText()`:
   - Primary path: `Smalot\PdfParser\Parser` (Composer package) reads the real PDF text layer.
   - Fallback: a regex scan of the raw PDF byte stream for `(...) Tj` / `[...] TJ` text-showing operators, used
     if the parser isn't available or throws.
5. Any previous resume for the user is deactivated (`is_active = 0`); the new one is inserted as `is_active = 1`.
6. `$_SESSION['resume_id']` / `resume_name` are set so the UI can show "Active resume: ___" without a DB hit.

### 7.2 Job search — `api/search_jobs.php`
1. Accepts a `GET` `query` string (default: `"software developer in Minneapolis"`) and forwards it to
   `jsearch.p.rapidapi.com/search-v2` with `num_pages=1&country=us&date_posted=all`, authenticated by
   `x-rapidapi-key` (from `JSEARCH_API_KEY`).
2. Normalizes each JSearch result into a flat shape (`title`, `company`, `location`, `description`, `source`,
   `employment_type`, `apply_url`, `is_remote`, `external_id`, …).
3. If the user is logged in, each normalized job is upserted into `job_listings`
   (`ON DUPLICATE KEY UPDATE fetched_at = NOW()`), and the row's own listing id is attached to the response so
   the frontend can reference it later.
4. The frontend (`jobs.php`) renders each result as a card with three actions: **Apply** (opens the real
   `apply_url` in a new tab), **Generate Tailored Resume**, **Generate Cover Letter**.
5. Platform filter buttons (LinkedIn/Indeed/Glassdoor/Monster) simply append the platform name as a keyword to
   the search query client-side — there's no dedicated per-platform API parameter.

### 7.3 AI resume tailoring — `api/generate_resume.php`
1. Requires an active session and a stored resume with non-empty `parsed_text` (returns a friendly error
   otherwise).
2. Builds a single prompt embedding the full resume text plus the job's title/company/location/type/description,
   instructing Claude to rewrite the resume to match that specific posting while staying truthful, one page,
   with Summary/Experience/Skills/Education sections.
3. Sends the prompt to `POST https://api.anthropic.com/v1/messages` (`model: claude-haiku-4-5-20251001`,
   `max_tokens: 1500`) using `CLAUDE_API_KEY`.
4. Parses the response defensively (handles several possible response shapes for resilience).
5. Looks up the matching `job_listings` row (by user/title/company) and inserts a row into `applications` with
   `resume_variant` populated.
6. Returns the generated text to the browser, which displays it in a modal with a "copy to clipboard" button
   (nothing is auto-submitted anywhere).

### 7.4 AI cover letter generation — `api/generate_cover_letter.php`
Mirrors 7.3 exactly, but the prompt asks for a persuasive one-page cover letter addressed to "the Hiring Manager"
and the result is stored in the `applications.cover_letter` column instead. Uses a PHP heredoc for prompt
construction and a smaller `max_tokens` (800).

### 7.5 Dashboard — `dashboard.php`
- Requires login.
- Pulls the 10 most recently fetched `job_listings` rows for the user (`ORDER BY fetched_at DESC LIMIT 10`).
- Computes two activity counters: total `job_listings` rows and total `applications` rows for the user.
- Renders "Matched"/"Applied" tabs (tab-switching is currently front-end only — both tabs show the same query
  results; there's no separate "applied" query yet) and a right-hand "Genie Tips" panel.
- The "Have AI agent apply" button on each job card is a UI placeholder (`alert(...)`) — it is **not** wired to
  the API yet.
- Match-percentage badges show a static "—" — there is no scoring/match algorithm implemented; JSearch results
  are not ranked against the resume in the current build.

### 7.6 Landing page — `index.php` / `assets/js/app.js`
Static marketing page: hero section, a 4-step "How it works" explainer, and a footer. "Upload Resume" and
"Connect LinkedIn" buttons both redirect unauthenticated visitors straight to `jobs.php` (which itself will
bounce to `login.php` once a protected action is attempted, since `jobs.php` requires a session).

---

## 8. API Reference

All endpoints live under `/api/` and require an active PHP session (`$_SESSION['user_id']`) except where noted.
Responses are JSON.

| Endpoint | Method | Auth required | Purpose | Key request fields | Key response fields |
|---|---|---|---|---|---|
| `api/search_jobs.php` | GET | No (but results only persist if logged in) | Search live job listings | `query`, `page` | `success`, `count`, `jobs[]` |
| `api/upload_resume.php` | POST (multipart) | Yes | Upload & parse a PDF resume | `resume` (file) | `success`, `resume_id`, `text_length`, `text_preview` |
| `api/generate_resume.php` | POST (JSON body) | Yes | Generate a tailored resume for one job | `title`, `company`, `location`, `description`, `employment_type` | `success`, `generated_resume` |
| `api/generate_cover_letter.php` | POST (JSON body) | Yes | Generate a cover letter for one job | same as above | `success`, `generated_cover_letter` |

All endpoints return `{"success": false, "error": "..."}` with an appropriate HTTP status (401/405/500) on
failure.

---

## 9. Configuration & Environment Variables

`config/env.php` is a tiny hand-written `.env` loader (no library dependency) that reads `RG/.env` and populates
`getenv()`/`$_ENV`/`$_SERVER`. `config/db.php` requires it, then opens a PDO connection.

| Variable | Purpose |
|---|---|
| `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` | MySQL connection (currently hardcoded in `config/db.php` rather than read from env — see Section 12) |
| `CLAUDE_API_KEY` | Anthropic API key used by both `generate_resume.php` and `generate_cover_letter.php` |
| `JSEARCH_API_KEY` | RapidAPI key for JSearch, used by `search_jobs.php` |
| `APP_ENV`, `APP_DEBUG` | Present in `.env.example`, not currently read anywhere in the app |
| `UPLOAD_DIR`, `MAX_UPLOAD_SIZE` | Present in `.env.example`; upload dir/size limit are currently hardcoded in `upload_resume.php` and client-side JS instead |

`.htaccess` denies direct web access to any dotfile (`.env`, `.git`, etc.) at the web root.

---

## 10. Security Measures

Implemented today:
- Passwords hashed with bcrypt (`password_hash` / `password_verify`).
- All SQL uses PDO prepared statements with bound parameters (no string-concatenated queries).
- Output escaped with `htmlspecialchars()` wherever user data is rendered into HTML (job titles, company names,
  error messages, etc.).
- Uploaded files are validated by both extension **and** magic-byte signature (`%PDF`) before being trusted.
- `.htaccess` blocks direct access to dotfiles.
- API endpoints gate on session state and reject wrong HTTP methods (405) before doing any work.

Gaps relative to `requirements.md`'s NFR-03 (flagged here so they're visible, not silently assumed done):

- `config/db.php` currently hardcodes DB credentials directly in the file rather than sourcing them from `.env`
  (the `.env`/`env.php` mechanism exists and is wired up, but `db.php`'s `DB_PASS` constant is a literal string).
  **This means a real-looking database password is committed to source control** — it should be rotated and
  moved into `.env` before any public/shared use of this repository.
- No account-deletion / GDPR-purge flow yet (FR data-deletion requirement is unmet).
- No email verification or password-reset-by-token flow yet.

---

## 11. Local Setup

1. **Requirements**: PHP 8.2+, MySQL 8.0+, Composer, a PHP-capable web server (Apache/XAMPP/MAMP or `php -S`).
2. **Install dependencies**: `composer install` (pulls `smalot/pdfparser`, `symfony/polyfill-mbstring`).
3. **Create the database**: run `RoleGenie/RoleGenie schema.sql` against MySQL (`CREATE DATABASE role_genie ...`
   plus all 5 tables).
4. **Configure environment**: copy `.env.example` to `.env` and fill in `CLAUDE_API_KEY` and `JSEARCH_API_KEY`.
   Update `config/db.php`'s connection constants (or refactor them to read from `.env` — see Section 12) to match
   your local MySQL credentials.
5. **Uploads folder**: ensure `uploads/resumes/` exists and is writable by the web server process.
6. **Run**: point your web server's document root at `RG/`, or run PHP's built-in server from that directory:
   `php -S localhost:8000`.
7. **Use it**: visit `/register.php` to create an account, then `/jobs.php` to upload a resume and search jobs.

---
