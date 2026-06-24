# Website and Correspondence Modules

## Architecture

The project remains a single Laravel modular monolith. The public website is owned by `modules/Website`, while official electronic correspondence is owned by `modules/Correspondence`.

`Website` owns CMS content, public pages, contact submissions, banners, FAQs, and website settings. It does not duplicate departments, specializations, instructors, or student registration records. Public academic and admission data is read through published Actions in the owning modules.

`Correspondence` owns official internal correspondence records, recipients, attachments metadata, replies, approval records, reference sequences, and status history. Ordinary workflows do not hard-delete official correspondence.

## Published Actions

- `Modules\Academic\Actions\ListPublicDepartments`
- `Modules\Academic\Actions\GetPublicAcademicCalendar`
- `Modules\Staff\Actions\ListPublicInstructors`
- `Modules\Student\Actions\GetPublicRegistrationStatus`
- `Modules\Student\Actions\GetNewStudentRegistrationInformation`

## Routes

Public website:

- `GET /` (`home`)
- `GET /pages/{slug}`
- `POST /contact`

Website administration:

- `GET /admin/website/pages`
- `POST /admin/website/pages`

Correspondence:

- `GET /correspondence/inbox`
- `GET /correspondence/sent`
- `GET /correspondence/create`
- `POST /correspondence`
- `GET /correspondence/{correspondence}`
- `POST /correspondence/{correspondence}/submit`
- `POST /correspondence/{correspondence}/approve`
- `POST /correspondence/{correspondence}/dispatch`
- `POST /correspondence/{correspondence}/reply`
- `POST /correspondence/{correspondence}/complete`
- `POST /correspondence/{correspondence}/archive`

The previous platform welcome page is available at `/portal`. The portal login remains at the existing Fortify/auth routes.

## Permissions

Website permissions:

- `website.pages.view`
- `website.pages.create`
- `website.pages.update`
- `website.pages.publish`
- `website.pages.archive`
- `website.settings.manage`
- `website.media.manage`
- `website.contact-submissions.view`

Correspondence permissions:

- `correspondence.view-own`
- `correspondence.create`
- `correspondence.send`
- `correspondence.reply`
- `correspondence.forward`
- `correspondence.approve`
- `correspondence.reject`
- `correspondence.return-for-changes`
- `correspondence.complete`
- `correspondence.archive`
- `correspondence.manage-templates`
- `correspondence.manage-categories`
- `correspondence.send-circulars`
- `correspondence.view-confidential`
- `correspondence.view-highly-confidential`
- `correspondence.audit.view`
- `correspondence.admin`

## Database Tables

Website:

- `website_pages`
- `website_posts`
- `website_banners`
- `website_faqs`
- `website_settings`
- `website_contact_submissions`

Correspondence:

- `correspondence_categories`
- `correspondence_reference_sequences`
- `correspondences`
- `correspondence_recipients`
- `correspondence_attachments`
- `correspondence_replies`
- `correspondence_status_histories`
- `correspondence_approvals`

## Correspondence Workflow

Drafts begin as `draft`. Submission assigns an atomic reference number using `correspondence_reference_sequences`, then moves to `submitted` or `pending_approval` when approval is required. Approved/submitted records can be dispatched, recipients receive timestamped delivery records, replies preserve thread history, and completed items can be archived.

Internal approval is stored as system approval metadata with approver, decision, timestamp, content hash, IP address, and user agent where available. It is not described as a legally qualified digital signature.

## Storage and Queues

Correspondence attachments store only metadata in the database. File binaries should be stored through Laravel Storage on a private disk and served through authorized download controllers or signed URLs when attachment upload/download endpoints are extended.

The project already has database queue tables. Run a worker for correspondence notifications, future attachment processing, previews, circular expansion, and email:

```bash
php artisan queue:work
```

## Deployment

Run migrations and seeders:

```bash
php artisan migrate
php artisan db:seed
```

Optional reference format:

```env
CORRESPONDENCE_REFERENCE_FORMAT=COR-{YEAR}-{SEQUENCE}
```

SSR was not enabled as part of this change. Public pages include Inertia titles and metadata; SSR can be added later as an infrastructure improvement if needed.

## Dependency Map

- `Website -> Academic` through published public Actions
- `Website -> Staff` through published public Actions
- `Website -> Student` through published public Actions
- `Correspondence -> User` for authenticated sender/recipient identities
- `Shared -> none of Website/Correspondence`
- No new dependency cycles are introduced.

## Tests and Checks

Targeted tests:

```bash
php artisan test modules/Website/Tests/Feature modules/Correspondence/Tests/Feature
```

Useful checks:

```bash
php artisan route:list --except-vendor
php artisan migrate --pretend
composer lint
npm run build
composer test
composer ci:check
```

## Remaining Improvements

- Add full CMS CRUD for posts, banners, FAQs, downloads, media, and settings.
- Add correspondence attachment upload/download controllers with private signed access.
- Add queued notifications and circular audience expansion jobs.
- Add advanced search filters with strict access scoping.
- Add print-friendly official correspondence HTML/PDF output.
- Add department routing configuration once organizational responsibility rules are finalized.
