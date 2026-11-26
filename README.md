# PathSeeker — Career Passport

PathSeeker is a complete Angular and Laravel career-guidance application for students, graduates, professionals, and administrators. It provides role-aware Career Passports, a deterministic interest assessment, career recommendations, a searchable Career Bank, learning content, bookmarks and notes, feedback, notifications, success stories, and administration tools.

This README is both the installation guide and the manual acceptance-test checklist. Follow it from top to bottom to review every page, function, API connection, and database-backed operation.

> Career recommendations are informational guidance, not guaranteed employment outcomes. Salary and demand values are seeded demonstration catalogue data and are not live labour-market data.

## 1. Technology and architecture

```text
Browser
  → Angular 22 SPA (frontend, port 4200)
  → Laravel 13 JSON API (backend, port 8000)
  → MySQL database: pathseeker (XAMPP, port 3306)
```

- Frontend: Angular 22.1, TypeScript 6, RxJS, Reactive Forms, SCSS
- Backend: Laravel 13.26, PHP 8.4, Sanctum token authentication, Eloquent
- Database: MySQL through XAMPP
- Tests: PHPUnit/Laravel feature tests and Angular/Vitest tests
- Passwords are hashed; API authorization is enforced server-side.
- Registered accounts must verify their email before protected features are available.

## 2. Prerequisites

- XAMPP with MySQL running
- PHP 8.3 or newer with Laravel-required extensions
- Composer 2
- Node.js 22 and npm 10
- A current Chrome, Edge, Firefox, or Safari browser

Verified development versions: PHP 8.4.24, Composer 2.9.5, Node 22.23.1, npm 10.9.8, Laravel 13.26.1, and Angular CLI 22.1.5.

## 3. Clean installation with MySQL

### 3.1 Create the database

Start MySQL in XAMPP. In phpMyAdmin, create a database named `pathseeker` using `utf8mb4_unicode_ci` or another Unicode collation.

### 3.2 Prepare the backend

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
```

Configure `backend/.env`:

```env
APP_NAME=PathSeeker
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:4200
FRONTEND_ORIGINS=http://localhost:4200,http://127.0.0.1:4200

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pathseeker
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@pathseeker.test
MAIL_FROM_NAME=PathSeeker
```

Use your real MySQL username/password when it differs from the usual XAMPP defaults.

Create all tables, insert the complete demonstration dataset, and expose uploaded files:

```bash
php artisan migrate:fresh --seed
php artisan storage:link
```

`migrate:fresh` deletes existing tables. For an existing database whose data must be preserved, use this instead:

```bash
php artisan migrate --seed
```

### 3.3 Prepare the frontend

```bash
cd ../frontend
npm install
```

## 4. Start the application

Keep XAMPP MySQL running and use two terminals.

Terminal 1:

```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

Terminal 2:

```bash
cd frontend
npm start
```

Open:

- Application: <http://localhost:4200>
- API health: <http://127.0.0.1:8000/api/health>

Expected health response:

```json
{
  "success": true,
  "message": "PathSeeker API is available.",
  "data": {
    "service": "pathseeker-api",
    "environment": "local"
  }
}
```

If the website loads but lists are empty, verify that MySQL is running and that the backend terminal has no database exception.

## 5. Demonstration accounts

Every seeded account below uses password `PathSeeker2026!`.

| Role | Email | Main review purpose |
|---|---|---|
| Administrator | `admin@pathseeker.test` | Dashboard, users, CRUD, moderation, feedback, announcements |
| Student | `student@pathseeker.test` | Student Career Passport and assessment |
| Graduate | `graduate@pathseeker.test` | Graduate-specific education fields |
| Professional | `professional@pathseeker.test` | Work experience and transition fields |

Additional populated users appear in the administrator user list. Public registration cannot create an administrator.

## 6. Database verification

Run these commands from `backend/` while MySQL is running.

### Check the configured connection

```bash
php artisan about
php artisan config:show database
```

Confirm that the default connection is `mysql` and the database is `pathseeker`.

### Check migrations

```bash
php artisan migrate:status
```

Every migration should show `Ran`.

### Check seeded records

```bash
php artisan tinker --execute="dump([
    'users' => App\\Models\\User::count(),
    'careers' => App\\Models\\Career::count(),
    'career_domains' => App\\Models\\CareerDomain::count(),
    'quiz_questions' => App\\Models\\QuizQuestion::count(),
    'resources' => App\\Models\\Resource::count(),
    'multimedia' => App\\Models\\Multimedia::count(),
    'success_stories' => App\\Models\\SuccessStory::count(),
]);"
```

The standard seed contains 12 users, 10 career domains, 40 careers, 20 quiz questions, 15 resources, 12 multimedia entries, and seeded success stories. Counts can increase after manual testing.

### Prove that browser actions reach MySQL

1. Note the bookmark count:

```bash
php artisan tinker --execute="dump(App\\Models\\Bookmark::count());"
```

2. Sign in as the student, open a career, and select **Save career**.
3. Run the command again. The count should increase.
4. Add a private note in `/app/saved` and verify the row:

```bash
php artisan tinker --execute="dump(App\\Models\\Bookmark::latest()->first(['id','user_id','bookmarkable_type','bookmarkable_id','note','updated_at']));"
```

The same approach can verify `QuizAttempt`, `MediaRating`, `Feedback`, `RecentView`, and `SuccessStory` records.

## 7. Public-page review checklist

No account is required for these routes.

| Page | URL | What to verify |
|---|---|---|
| Home | `/` | Hero, calls to action, feature sections, responsive navigation, footer |
| Career Bank | `/careers` | Database careers, search/autocomplete, filters, sorting, pagination, compare selection |
| Career detail | `/careers/:slug` | Breadcrumbs, description, responsibilities, skills, pathway, salary disclaimer, related navigation |
| Compare | `/compare` | Up to three selected careers displayed side-by-side |
| Resources | `/resources` | Search, category cards, audience/type metadata, tags |
| Resource detail | `/resources/:slug` | Preview/open action, download counting, saving, breadcrumbs |
| Multimedia | `/multimedia` | Video/audio/explainer cards, search and categories |
| Multimedia detail | `/multimedia/:slug` | Playback link, transcript toggle, rating summary, related careers |
| Success stories | `/stories` | Card grid and domain-oriented content |
| Story detail | `/stories/:slug` | Education-to-outcome timeline, challenges, advice, moderation-safe content |
| About | `/about` | Purpose and recommendation disclaimer |
| Pages menu | Native Pages dropdown in the public shell | Links to all primary pages and grouped content routes |
| Not found | Any invalid URL | Purposeful 404 page instead of a blank screen |

### Career Bank filter test

On `/careers` verify all of the following:

- Keyword search updates results after a short debounce.
- Domain and demand filters work.
- Skill, education, experience, minimum salary, and maximum salary filters reach the API.
- Sort supports title, reverse title, salary, and demand.
- **Save these preferences** stores the selected filters; reload the page to confirm restoration.
- **Clear all** removes active filters.
- Selecting careers for comparison persists while navigating.

## 8. Authentication and account review

| Function | URL/action | Expected result |
|---|---|---|
| Registration | `/auth/register` | Two-step identity and Student/Graduate/Professional role selection |
| Email verification | Not required | Registration signs in directly and protected APIs use Sanctum auth |
| Login | `/auth/login` | Valid account receives a Sanctum token and correct role dashboard |
| Invalid login | Login with wrong password | Validation error without revealing sensitive details |
| Forgot password | `/auth/forgot-password` | Generic response prevents account enumeration |
| Reset password | Link produced by mail/log | Tokenized reset changes password and revokes existing tokens |
| Logout | Application navigation | Current token is revoked and protected pages require login |
| Authorization | Visit `/admin` as student | Access-denied page; API also rejects the request |

### Test local email verification

With `MAIL_MAILER=log`, Laravel writes email content to:

```text
backend/storage/logs/laravel.log
```

1. Register a new user.
2. Open the newest verification URL from the log in the browser.
3. The signed URL marks `email_verified_at` and redirects to the Career Passport.
4. To inspect the database value:

```bash
php artisan tinker --execute="dump(App\\Models\\User::where('email','YOUR_EMAIL')->value('email_verified_at'));"
```

For real delivery, configure SMTP values in `.env` and run `php artisan config:clear`.

## 9. Signed-in user review checklist

Sign in as `student@pathseeker.test` unless a different role is specified.

### Overview dashboard — `/app`

Verify:

- Personalized greeting and real Career Passport completion
- Latest quiz result and ranked matches
- Saved careers/content summary
- Recent exploration history
- Trending careers based on internal stored engagement
- Recommended content and videos

### Career Passport — `/app/profile`

Verify:

- Name, country, career goal, education, skills, and interests load from MySQL.
- Student fields show current subjects/field.
- Graduate account shows degree, field of study, and graduation year.
- Professional account shows job title, industry, years of experience, and transition goal.
- Selected skills allow proficiency values from Beginner to Expert.
- Saving updates the database and recalculates completion from stored evidence.

### Discover — `/app/discover`

Verify the full Career Bank checklist plus:

- Opening a career records a persistent recent view.
- Saving a career creates a bookmark.
- Recommendations and navigation remain authenticated.

### Interest assessment — `/app/quiz`

Verify:

- Twenty database-backed Likert questions appear in multiple steps.
- Progress, Back, Next, selection, and save states work.
- Reloading an unfinished assessment resumes saved answers.
- Completion creates an immutable result and ten ranked recommendations.
- Result factors disclose deterministic quiz, interest, skill, and interaction weighting.
- Retaking creates history; the report compares top dimensions with earlier attempts.

### Saved Center — `/app/saved`

Verify:

- Career, resource, multimedia, and story bookmarks can appear together.
- Search filters saved items.
- Private notes save to MySQL.
- Remove deletes only the signed-in user's bookmark.
- **Export / Save PDF** opens the browser print dialog with print-friendly content.
- **Share** uses the Web Share API when supported and otherwise opens an email draft.

### Story submission — `/app/stories/submit`

Verify:

- A user can submit a five-stage success story.
- Submission history shows pending/approved/rejected state.
- A pending story does not appear publicly before administrator approval.

### Feedback — `/app/feedback`

Verify:

- Bug, suggestion, question, and other categories submit successfully.
- The user sees their feedback history, workflow status, and administrator response.

### Notifications — `/app/notifications`

Verify:

- Announcements and feedback-response notifications appear.
- Individual read and **mark all read** update persistent read timestamps.
- Notification links navigate to the relevant page.

### Accessibility and responsive behavior

Verify in public and authenticated layouts:

- System, Light, and Dark themes
- Default, Larger, and Largest font sizes
- Keyboard-visible focus and semantic labels
- Breadcrumbs on detail pages
- Loading skeletons/spinners and readable empty/error states
- Reduced-motion browser preference
- Mobile layouts at approximately 360–390 px width

## 10. Administrator review checklist

Sign in as `admin@pathseeker.test`.

| Page | URL | What to verify |
|---|---|---|
| Admin overview | `/admin` | Real KPIs, users by role, registrations, popular careers, feedback distribution |
| Users | `/admin/users` | Search/filter, account details, activate/deactivate, self-deactivation protection |
| Careers | `/admin/careers` | Search and complete create/edit/delete/publish workflow, skill weights, market context |
| Quiz | `/admin/quiz` | Create/edit/deactivate questions and scored options; historical answers remain safe |
| Resources | `/admin/resources` | Create/edit/delete, tags, audience, publication, validated PDF/image upload or URL |
| Multimedia | `/admin/media` | Metadata, transcript, tags, related careers, publication, safe deletion |
| Stories | `/admin/stories` | Pending/approved/rejected queues, review notes, approve/reject actions |
| Feedback | `/admin/feedback` | Type/status/priority filters, response, status changes, automatic user notification |
| Announcements | `/admin/announcements` | Draft/publish messages targeted to everyone or a selected user role |
| Invalid admin route | `/admin/not-real` | Admin-specific 404 page |

### Recommended administrator end-to-end test

1. Create a draft career and confirm it is absent from the public Career Bank.
2. Publish it and confirm it appears publicly.
3. Edit it and confirm the public detail changes.
4. Create a resource and multimedia item with tags.
5. Approve a pending success story and confirm it becomes public.
6. Respond to student feedback and confirm the student receives a notification.
7. Publish a student-only announcement and confirm role-targeted delivery.
8. Delete only newly created test records that have no protected activity history.

## 11. API smoke tests

Public endpoints can be checked directly:

```bash
curl -fsS http://127.0.0.1:8000/api/health
curl -fsS http://127.0.0.1:8000/api/career-domains
curl -fsS http://127.0.0.1:8000/api/career-options
curl -fsS "http://127.0.0.1:8000/api/careers?domain=technology&per_page=3"
curl -fsS http://127.0.0.1:8000/api/resources
curl -fsS http://127.0.0.1:8000/api/multimedia
curl -fsS http://127.0.0.1:8000/api/success-stories
```

Protected API calls require the bearer token stored after login:

```http
Authorization: Bearer YOUR_SANCTUM_TOKEN
Accept: application/json
```


## 12. Automated verification

Backend:

```bash
cd backend
php artisan test --compact
./vendor/bin/pint --test app routes database
php artisan route:list --except-vendor
php artisan route:cache
php artisan route:clear
```

Frontend:

```bash
cd frontend
npm test -- --watch=false
npm run build
```

Current verified result:

- 27 Laravel tests passed
- 128 Laravel assertions passed
- Laravel Pint passed
- Production route caching passed
- 2 Angular tests passed
- Angular production build passed

Do not leave a development route cache enabled while editing routes.

## 13. Database tables and persistence map

| Area | Main tables |
|---|---|
| Accounts | `users`, `personal_access_tokens`, `password_reset_tokens` |
| Career Passport | `user_profiles`, `skills`, `user_skills`, `interests`, `user_interests` |
| Career Bank | `career_domains`, `careers`, `career_skills`, career interest/dimension weight tables |
| Assessment | `quiz_questions`, `quiz_options`, `quiz_attempts`, `quiz_answers`, `quiz_results`, `career_recommendations` |
| Personalization | `bookmarks`, `recent_views` |
| Content | `resources`, `multimedia`, `tags`, tag pivots, `media_ratings`, `success_stories` |
| Communication | `feedback`, `notifications`, `announcements` |
| Infrastructure | `cache`, `jobs`, `sessions` |

Migrations in `backend/database/migrations/` are the authoritative schema.

## 14. Backup and restore

Export through phpMyAdmin using **Export → Quick → SQL**, or use:

```bash
mysqldump -h 127.0.0.1 -P 3306 -u root -p pathseeker > pathseeker.sql
```

With a blank XAMPP password, omit `-p`.

Restore with phpMyAdmin Import or:

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p pathseeker < pathseeker.sql
```

Do not commit exports containing real user information.

## 15. Troubleshooting

| Problem | Resolution |
|---|---|
| `Connection refused` | Start MySQL in XAMPP and confirm port 3306 |
| `Access denied for user` | Correct `DB_USERNAME`/`DB_PASSWORD`, then run `php artisan config:clear` |
| `Unknown database pathseeker` | Create the database in phpMyAdmin, then migrate |
| Tables exist but demo data is absent | Run `php artisan db:seed` |
| Frontend shows API/CORS error | Keep API at 127.0.0.1:8000 and match `FRONTEND_ORIGINS` exactly |
| Protected request returns 403 after new registration | Verify the email using the logged link or configured SMTP message |
| Verification/reset email is missing | With log mailer, inspect `backend/storage/logs/laravel.log` |
| Uploaded file is not visible | Run `php artisan storage:link` and check storage permissions |
| Angular dependencies mismatch | Run `npm install` inside `frontend/` using its lockfile |
| Route change does not appear | Run `php artisan route:clear` |
| Development ports are occupied | Stop the old process or select matching frontend/backend ports and update configuration |

## 16. Repository and documentation map

```text
backend/                    Laravel API, migrations, seeders, uploads, and tests
frontend/                   Angular SPA and frontend tests
Career Passport_...pdf      Original Software Requirements Specification
```

## 17. Environment-dependent submission items

The source code, setup instructions, database schema/migrations, seed data, credentials, diagrams, and report outline are included. These items require human or deployment-environment work:

- Configure real SMTP credentials for production email delivery.
- Host the Angular application, Laravel API, and MySQL database if a public URL is required.
- Record the mandatory demonstration video requested by the SRS.
- Export a final SQL snapshot if the evaluator requires a `.sql` file in addition to migrations.
- Replace demonstration credentials and set `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, secure cookies, and production caching before public deployment.

