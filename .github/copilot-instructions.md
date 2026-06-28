# Copilot Instructions for WatchAdmin

## Project Overview
- **Framework:** Laravel (PHP)
- **Purpose:** Web application for watch administration, likely including user management, deals, notifications, and integrations with external services.

## Architecture & Key Components
- **app/**: Main application logic
  - **Http/Controllers/**: Route handlers and business logic
  - **Models/**: Eloquent ORM models for database tables (e.g., `Deal`, `User`, `WatchBrand`)
  - **Helpers/**: Utility functions and classes (see `helpers.php`, `SanitizeHelper.php`)
  - **Jobs/**: Queueable background tasks (e.g., invoice generation, notifications)
  - **Services/**: Integrations with external APIs (e.g., `MyobService.php`, `ShipStationService.php`, `XeroService.php`)
  - **Pdf/**: Custom PDF generation logic
  - **Providers/**: Laravel service providers for bootstrapping features
- **routes/**: Route definitions (`web.php`, `auth.php`, etc.)
- **resources/views/**: Blade templates for frontend rendering
- **config/**: Application configuration (e.g., `constants.php`, `services.php`)

## Developer Workflows
- **Start Local Server:**
  - Use `php artisan serve` (default Laravel dev server)
- **Database:**
  - Migrations: `php artisan migrate`
  - Seeders: `php artisan db:seed`
  - SQL dump: `watchadmin.sql` (for manual import)
- **Build Frontend Assets:**
  - Use Vite: `npm run dev` (see `vite.config.js`)
  - Tailwind CSS: Configured via `tailwind.config.js`
- **Run Tests:**
  - PHPUnit: `vendor/bin/phpunit` or `php artisan test`
- **Queue/Jobs:**
  - Start worker: `php artisan queue:work`

## Project-Specific Patterns & Conventions
- **Helpers:** Use `app/Helpers/helpers.php` for global functions; prefer static methods in helper classes for organization.
- **Services:** External API logic is encapsulated in `app/Services/` (e.g., Myob, ShipStation, Xero). Each service handles its own authentication and data mapping.
- **Jobs:** Background tasks are defined in `app/Jobs/` and dispatched from controllers/services.
- **PDF Generation:** Custom logic in `app/Pdf/CustomPdf.php`.
- **Config:** Use `config/constants.php` for project-wide constants.
- **Uploads:** User-uploaded files are stored in `public/uploads/`.

## Integration Points
- **External APIs:**
  - Myob, ShipStation, Xero (see respective service classes)
- **Notifications:** Jobs for sending notifications on deal creation/update
- **Authentication:** Managed via Laravel's built-in system, with customizations in `app/Providers/AuthServiceProvider.php`

## Example Patterns
- **Dispatching a Job:**
  ```php
  GenerateInvoiceFromDealJob::dispatch($deal);
  ```
- **Using a Service:**
  ```php
  app(MyobService::class)->syncDeal($deal);
  ```
- **Accessing a Helper:**
  ```php
  AdvancedHelper::sanitizeInput($input);
  ```

## Key Files & Directories
- `app/Helpers/` — Utility functions
- `app/Services/` — External API integrations
- `app/Jobs/` — Background processing
- `config/constants.php` — Project constants
- `public/uploads/` — File uploads
- `resources/views/` — Blade templates
- `routes/web.php` — Main route definitions

---

**For AI agents:**
- Follow Laravel conventions unless project-specific patterns are documented above.
- Reference service, job, and helper usage examples for cross-component logic.
- When in doubt, check for custom logic in `app/Helpers/`, `app/Services/`, and `config/constants.php`.

---

_If any section is unclear or missing, please provide feedback for further refinement._
