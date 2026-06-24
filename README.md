# Laravel Modular Starter

A Laravel starter kit organized as a modular monolith. Each feature lives in its
own module under `modules/`, following a few light DDD ideas — bounded contexts,
aggregates, domain events — without the heavy ceremony. No repository layer over
Eloquent, no Domain/Application/Infrastructure split. Eloquent models are the
aggregates; modules are the contexts around them.

It started from the official [Laravel Vue starter kit](https://github.com/laravel/vue-starter-kit)
and keeps the same stack and auth — it's that kit reorganized into modules.

**Stack:** Laravel 13 · Inertia + Vue 3 (TypeScript) · Tailwind CSS · Laravel
Fortify (login, registration, email verification, 2FA, passkeys) · Wayfinder
(typed routes & actions) · Pest.

## Quick start

```bash
composer setup        # install deps, create .env, generate key, migrate, build assets
composer dev          # run server + queue + vite together (hot reload)

# checks
composer test         # config clear + Pint (check) + Pest
composer ci:check     # full gate, same as CI: ESLint + Prettier + vue-tsc + tests

# auto-fix
composer lint         # fix PHP code style (Pint)
npm run lint          # fix JS/Vue lint (ESLint)
npm run format        # fix formatting (Prettier)
```

## Docker And CI

The repo now includes a Docker Compose stack with `app`, `web`, `db`, `redis`,
and `queue` services:

```bash
docker compose up --build
```

The app is exposed through Nginx on `http://localhost:8080`.

For Docker, start from `.env.docker.example` and copy it to `.env` before the
first `docker compose up --build` if you want the container defaults outside the
host-specific compose overrides.

GitHub Actions now includes:

- `ci.yml` for `composer install`, `npm ci`, `npm run types:check`, `npm run build`, and `php artisan test`
- `docker-publish.yml` for publishing a container image to GHCR on tags or releases

Generate code straight into a module with [`make:module`](#generating-files-into-a-module-makemodule):

```bash
php artisan make:module User model Product --migration --factory
```

## Layout

```
modules/
├── Academic/
│   ├── Database/
│   │   ├── Factories/ (AcademicSemesterFactory, CourseFactory, CourseClassFactory, DepartmentFactory, SpecializationFactory)
│   │   └── Migrations/ (8 migration files for courses, departments, etc.)
│   ├── Http/
│   │   └── Controllers/ (CourseController, DepartmentController, SemesterController, SpecializationController, StudyGroupController)
│   ├── Models/ (AcademicSemester, Course, CourseClass, Department, Specialization, StudyGroup)
│   ├── Providers/ (AcademicServiceProvider)
│   └── Routes/ (web.php)
├── Auth/
│   ├── Actions/ (CreateNewUser, ResetUserPassword)
│   ├── Database/
│   │   └── Migrations/ (2024_01... migration)
│   ├── Http/
│   │   ├── Controllers/ (SecurityController)
│   │   └── Requests/ (PasswordUpdateRequest, TwoFactorAuthRequest)
│   ├── Providers/ (AuthServiceProvider)
│   ├── Routes/ (web.php)
│   └── Tests/
│       └── Feature/ (AuthenticationTest, EmailVerificationTest, PasswordConfirmationTest, PasswordResetTest, RegistrationTest, SecurityTest, TwoFactorChallengeTest, VerificationNoticeTest)
├── Exam/
│   ├── Database/
│   │   ├── Factories/ (ExamAllocationFactory, ExamHallFactory, ExamScheduleFactory)
│   │   └── Migrations/ (3 migration files)
│   ├── Http/
│   │   └── Controllers/ (ExamAllocationController, ExamHallController, ExamScheduleController)
│   ├── Models/ (ExamAllocation, ExamHall, ExamSchedule)
│   ├── Providers/ (ExamServiceProvider)
│   └── Routes/ (web.php)
├── Platform/
│   ├── Console/
│   │   ├── Commands/ (MakeModule, ModuleAnalyze, ModuleClear, ModuleList)
│   │   ├── Concerns/ (ScansModules)
│   │   └── stubs/ (action.stub)
│   ├── Database/
│   │   ├── Migrations/ (0001_01... migrations)
│   │   └── Seeders/ (DatabaseSeeder, SuperAdminSeeder)
│   ├── Http/
│   │   └── Middleware/ (HandleApiRequest, HandleInertia)
│   ├── Providers/ (PlatformServiceProvider)
│   ├── Routes/ (console.php, web.php)
│   └── Tests/
│       └── Feature/ (DashboardTest, WelcomeTest)
├── Shared/
│   ├── Concerns/ (PasswordValidationRules)
│   ├── Helpers/ (Helpers.php)
│   ├── Http/
│   │   └── Controllers/ (Controller)
│   └── Tests/
│       └── Feature/ (HelpersTest)
├── Staff/
│   ├── Database/
│   │   ├── Factories/ (InstructorFactory)
│   │   └── Migrations/ (2026_06... migration)
│   ├── Http/
│   │   └── Controllers/ (InstructorController)
│   ├── Models/ (Instructor)
│   ├── Providers/ (StaffServiceProvider)
│   └── Routes/ (web.php)
├── Student/
│   ├── Actions/ (CalculateCGPAAction, CheckAcademicWarningAction, CheckGraduationAction, ImportSemesterResultsAction, PromoteStudentAction, RecalculateSemesterAction, RecordCourseGradeAction)
│   ├── Database/
│   │   ├── Factories/ (CourseEnrollmentFactory, DepartmentTransferFactory, RegistrationSuspensionFactory, StudentFactory, StudentSemesterSummaryFactory)
│   │   └── Migrations/ (6 migration files)
│   ├── Http/
│   │   └── Controllers/ (EnrollmentController, GradeController, PromotionController, ResultImportController, StudentRegistrationController, StudentStatusController, StudentTransferController)
│   ├── Models/ (AcademicWarning, CourseEnrollment, DepartmentTransfer, RegistrationSuspension, Student, StudentSemesterSummary)
│   ├── Providers/ (StudentServiceProvider)
│   └── Routes/ (web.php)
└── User/
    ├── Actions/ (RegisterUser)
    ├── Concerns/ (ProfileValidationRules)
    ├── Database/
    │   ├── Factories/ (UserFactory)
    │   ├── Migrations/ (0001_01... and 2025_08... migrations)
    │   └── Seeders/ (UserSeeder)
    ├── Events/ (UserRegistered)
    ├── Http/
    │   ├── Controllers/ (ProfileController)
    │   └── Requests/ (ProfileDeleteRequest, ProfileUpdateRequest)
    ├── Listeners/ (LogRegisteredUser)
    ├── Models/ (User)
    ├── Providers/ (UserServiceProvider)
    ├── Routes/ (web.php)
    └── Tests/
        └── Feature/ (ProfileUpdateTest)
```

There's no `app/` directory — application code lives in `modules/`. The root keeps
only the framework skeleton: `bootstrap/`, `config/`, `database/` (the dev sqlite
file), `public/`, `resources/`, and `tests/` (the test harness).

### Naming conventions

The root folder is lowercase `modules/`; everything inside is PascalCase — module
names and sub-folders alike (`Routes/`, `Database/`, …). Each module wires those
paths in its own service provider.

## Namespacing

`composer.json` maps `Modules\` → `modules/`:

```json
"autoload": {
    "psr-4": {
        "Modules\\": "modules/"
    }
}
```

So `modules/User/Models/User.php` is `Modules\User\Models\User`. New classes are
picked up automatically (PSR-4) — no `composer dump-autoload` needed.

## Module types

| Kind | Example | Owns | Depends on |
| ------ | --------- | ------ | ------------ |
| Bounded context | `User` | An aggregate + its rules, events, HTTP | Shared, other contexts (one-way) |
| Thin capability | `Auth` | Adapts a package (Fortify); no aggregate | The context it operates on |
| App shell | `Platform` | Host surface + global middleware + app defaults + infra migrations + seeder | Anything it renders |
| Shared kernel | `Shared` | Cross-cutting code the contexts share (traits, base Controller) | Nothing |

## The dependency rule

Dependencies point one way and never form a cycle:

```
        Auth ──────────▶ User
          │               │
          └────▶ Shared ◀─┘          Platform (host shell — stands alone)
```

- `Auth → User`, never `User → Auth`.
- Anyone may depend on `Shared`; `Shared` depends on nothing.
- A module never imports another module's internal classes directly — cross-module
  work goes through a published Action or event.

## How modules communicate

Two ways for one module to reach another:

| Use a direct call (Action) when… | Use an event when… |
| --- | --- |
| You need a return value | You don't need a return value |
| The flow depends on the result (sync) | It's a side-effect / reaction |
| 1-to-1 | 1-to-many, decoupled |

Both show up in registration — `Auth`'s Fortify adapter calls `User`'s
`RegisterUser` action (it needs the user back), which then fires a domain event:

```php
$user = User::create([...]);
event(new UserRegistered($user));   // anyone can react; User doesn't know who
```

Listeners are wired in the owning module's provider. Make a listener
`implements ShouldQueue` to run it off the request cycle.

## Adding a new module

1. Create `modules/<Context>/` with the sub-folders you need.
2. Add a `Providers/<Context>ServiceProvider.php` and load what it owns in `boot()`:

   ```php
   public function boot(): void
   {
       $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

       Route::middleware('web')->group(function () {
           $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
       });
   }
   ```

3. Register the provider in `bootstrap/providers.php`.
4. Models live outside `App\Models`, so declare the factory on the model:

   ```php
   protected static function newFactory(): Factory
   {
       return MyModelFactory::new();
   }
   ```

5. Tests in `Tests/Feature` and `Tests/Unit` are auto-discovered — no per-module config.
6. Seeders go in `Database/Seeders/`; register them in `Platform`'s `DatabaseSeeder`.

## Generating files into a module (`make:module`)

Laravel's `make:*` generators target `app/` and don't know about modules.
`make:module` wraps any of them and writes the result straight into a module
(rewriting the namespace), without ever creating an `app/` directory.

```bash
php artisan make:module <module> <type> [name] [options]

# examples
php artisan make:module User model Product --migration --factory
php artisan make:module User controller ProductController --resource
php artisan make:module User request StoreProductRequest
php artisan make:module User migration create_products_table
php artisan make:module Auth listener SendWelcomeEmail --event=UserRegistered
php artisan make:module User action RegisterUser     # custom; no native make:action
```

- `type` is the generator name without `make:` (`model`, `controller`, `request`, …).
- Interactive mode works like the native generators (it prompts for name/type).
- Generators that don't target `app/`/`database/` (`test`, `view`, `config`, the
  `*-table` infra migrations) aren't domain code — run those natively.

## Tests

Tests live with their module under `modules/<Context>/Tests/`. Discovery is wired
once in `phpunit.xml` (a `Modules` testsuite) and `tests/Pest.php`, so a new module
needs no test config — just add files under `Tests/`. The root `tests/` keeps only
the harness (`Pest.php`, `TestCase`, `phpunit.xml`).
