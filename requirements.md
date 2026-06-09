## 5. Functional Requirements

### FR-01: User Account & Profile Management

- Users register with email and password; login is session-based (PHP sessions)
- Profile fields: full name, email, phone, current title, years of experience, target roles, desired salary, location preference, work type (remote / hybrid / on-site)
- Users upload a resume in PDF or Markdown (.md) format — stored on the Bluehost file system
- Users optionally enter a LinkedIn profile URL for the Claude agent to read via Claude Connectors
- Password reset via emailed token; email verification on registration

---

### FR-02: AI-Powered Job Discovery (Claude Agent)

- On demand (user clicks 'Find Jobs'), the system calls the Claude AI Agent
- The agent receives the user's profile, parsed resume text, and LinkedIn URL as context
- Claude uses Claude Connectors to search job sources (LinkedIn Jobs, Indeed, ZipRecruiter, etc.)
- The agent returns a ranked list of matching job listings
- The user can configure the result count: 10, listings (stored in user preferences)
- Each listing includes: job title, company, location, employment type, salary (if available), match score, and source URL

---

### FR-03: Per-Job AI Preparation (Claude Agent)

- For each listed job, the user can click 'Prepare Application'
- The Claude agent generates a tailored cover letter based on the job description and the user's profile/resume
- The agent produces a customized resume variant that highlights relevant experience for that specific role
- Both documents are displayed for the user to review and edit before applying
- The user can regenerate either document (the agent will produce a new version)
- Prepared documents are stored in MySQL linked to the user and the job listing

---

### FR-04: Agent-Driven Application Submission

- After reviewing prepared documents, the user can click 'Apply via Agent'
- The Claude agent uses Claude Connectors to navigate the employer's application page
- The agent fills out the application form using the user's profile and prepared documents
- The agent reports the result: submitted successfully, requires manual step, or failed
- The application record is updated in MySQL with status and timestamp
- Users receive an in-app notification and email confirmation upon successful submission

---

### FR-05: Application Tracking Dashboard

- Dashboard displays all jobs: discovered, prepared, applied, viewed, interview, rejected
- Filterable by status, date range, and source platform
- User can add personal notes to any application record
- User can manually mark status updates (e.g. received interview request outside the platform)

---

### FR-06: Configuration & Preferences

- Result count: user selects 10 jobs per search
- Job type filter: full-time, part-time, contract, internship
- Remote preference: remote only, hybrid, on-site, no preference
- Salary floor: minimum acceptable salary
- Location: city/state/ZIP or remote-only

---

### FR-07: Reminder

- After applying for a job, user is sent email reminder apply job application and to follow-up, does so in intervals of 1 day, 3 days, and a week

---

## 6. Non-Functional Requirements

### NFR-01: Hosting & Environment (Bluehost)

- Deployed on Bluehost VPS or shared hosting with PHP 8.2+ and MySQL 8.0+
- PHP configured with cURL enabled (for Anthropic API calls)
- File uploads stored in a non-web-accessible directory under the Bluehost account
- SSL/TLS via Bluehost's free Let's Encrypt certificate

---

### NFR-02: Performance

- Claude API calls are asynchronous where possible (JS fetch with loading indicators)
- MySQL queries use indexed columns; no full table scans on the hot path

---

### NFR-03: Security

- Passwords hashed with bcrypt (password_hash() PHP built-in)
- All Claude API keys and database credentials stored in a .env file outside the web root
- SQL injection prevented via PDO prepared statements throughout
- XSS prevented via htmlspecialchars() on all user-supplied output
- CSRF tokens on all state-changing forms
- Resume files validated for MIME type before storage; served via authenticated PHP script

---

### NFR-04: Compliance

- GDPR / CCPA: account deletion purges all user data and uploaded files
- Claude API usage complies with Anthropic's usage policies
- All automated application submissions include disclosure to the employer where required
