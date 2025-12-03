# Project Structure — project-pal

Generated: 2025-12-02
Branch: `integrasisystem`

This document lists the repository structure (application files, config, resources, and important top-level files).

---

## Root
- `artisan`
- `README.md`
- `backup.sql`
- `composer.json`
- `composer.lock`
- `package.json`
- `phpunit.xml`
- `optimize.bat`
- `.gitignore`
- `.gitattributes`
- `.editorconfig`
- `.htaccess`
- `vite.config.js`

## app/
- `Helpers/`
  - `ActivityLogger.php`
  - `AuditLogger.php`
- `Http/`
  - `Controllers/`
    - `Controller.php`
    - `DashboardController.php`
    - `DetailApprovalController.php`
    - `DesainController.php`
    - `DesainListProjectController.php`
    - `CheckpointTransitionController.php`
    - `AccountingController.php`
    - `NotificationController.php`
    - `InspectionController.php`
    - `EvatekController.php`
    - `VendorEvatekController.php`
    - `SupplyChainController.php`
    - `SekdirController.php`
    - `ProjectController.php`
    - `ProcurementController.php`
    - `PaymentController.php`
    - `UMS/`
      - `UsersController.php`
      - `SettingsController.php`
      - `AuditLogController.php`
      - `AdminScopeController.php`
      - `ActivityLogController.php`
  - `Middleware/`
    - `OptimizeMiddleware.php`
- `Listeners/`
  - `LogFailedLogin.php`
  - `LogSuccessfulLogin.php`
- `Models/`
  - `Approval.php`
  - `Checkpoint.php`
  - `Contract.php`
  - `Department.php`
  - `Division.php`
  - `EvatekItem.php`
  - `EvatekRevision.php`
  - `Hps.php`
  - `InspectionReport.php`
  - `Item.php`
  - `Negotiation.php`
  - `Notification.php`
  - `PaymentSchedule.php`
  - `Procurement.php`
  - `ProcurementProgress.php`
  - `Project.php`
  - `RequestProcurement.php`
  - `Vendor.php`
  - `User.php`
  - `UMS/`
    - `Setting.php`
    - `AuditLog.php`
    - `AdminScope.php`
    - `ActivityLog.php`
- `Providers/`
  - `AppServiceProvider.php`
- `Services/`
  - `CheckpointTransitionService.php`
  - `CheckpointIconService.php`

## bootstrap/
- `app.php`
- `providers.php`
- `cache/` (`.gitignore`)

## config/
- `app.php`
- `auth.php`
- `cache.php`
- `database.php`
- `filesystems.php`
- `logging.php`
- `mail.php`
- `queue.php`
- `services.php`
- `session.php`

## database/
- `factories/`
  - `UserFactory.php`
- `migrations/`
  - `2025_00_10_075910_create_divisions_table.php`
  - `2025_01_01_010000_create_users_table.php`
  - `2025_01_14_080030_create_sessions_table.php`
  - `2025_11_10_075915_create_projects_table.php`
  - `2025_11_10_075919_create_checkpoints_table.php`
  - `2025_11_10_075924_create_vendors_table.php`
  - `2025_11_10_075941_create_departments.php`
  - `2025_11_10_075942_create_procurment.php`
  - `2025_11_10_075943_create_request_procurement_table.php`
  - `2025_11_10_080003_create_items_table.php`
  - `2025_11_10_080336_create_contracts.php`
  - `2025_11_10_075945_create_procurement_progress_table.php`
  - `2025_11_13_025356_create_payment_schedules_table.php`
  - `2025_11_13_030119_create_inspection_reports_table.php`
  - `2025_11_19_065054_add_status_to_items_table.php`
  - `2025_11_21_020000_create_approvals_table.php`
  - `2025_11_26_034028_alter_procurement_progress_status_enum.php`
  - `2025_11_27_161714_create_audit_logs_table.php`
  - `2025_11_27_161958_make_actor_user_id_nullable_in_audit_logs_table.php`
  - `2025_11_27_162150_create_admin_scopes_table.php`
  - `2025_11_27_162251_create_settings_table.php`
  - `2025_12_01_015241_create_evatek_items_table.php`
  - `2025_12_01_022029_add_vendor_id_to_users_table.php`
  - `2025_12_01_083713_create_evatek_revisions_table.php`
  - `2025_12_01_152714_create_activity_logs_table.php`
- `seeders/`
  - `DatabaseSeeder.php`
  - `UserSeeder.php`
  - `CheckpointSeeder.php`
  - `DivisionSeeder.php`
  - `DepartmentSeeder.php`
  - `ProjectSeeder.php`
  - `UpdateUsersWithVendorIdSeeder.php`

## public/
- `index.php`
- `robots.txt`
- `.htaccess`
- `favicon.ico`
- `images/`
  - `logo-pal.png`
  - `assetimgkapal1.jpg`

## resources/
- `css/`
  - `app.css`
- `js/`
  - `bootstrap.js`
  - `app.js`
- `views/`
  - `welcome.blade.php`
  - `vendor/`
    - `index.blade.php`
  - `layouts/`
    - `app.blade.php`
  - `ums/`
    - `layouts/`
      - `app.blade.php`
    - `users/`
      - `index.blade.php`
      - `create.blade.php`
      - `edit.blade.php`
    - `settings/`
      - `index.blade.php`
      - `create.blade.php`
      - `edit.blade.php`
    - `audit_logs/`
      - `index.blade.php`
      - `show.blade.php`
    - `activity_logs/`
      - `index.blade.php`
      - `show.blade.php`
    - `admin_scopes/`
      - `index.blade.php`
      - `create.blade.php`
      - `edit.blade.php`
  - `procurements/`
    - `index.blade.php`
    - `create.blade.php`
    - `edit.blade.php`
    - `show.blade.php`
    - `by-project.blade.php`
  - `payments/`
    - `index.blade.php`
  - `sekdir/`
    - `approval.blade.php`
    - `approval-detail.blade.php`
  - `desain/`
    - `list-project.blade.php`
    - `review-evatek.blade.php`
    - `input-item.blade.php`
    - `daftar-permintaan.blade.php`
  - `supply_chain/`
    - `dashboard.blade.php`
    - `vendor/`
      - `pilih.blade.php`
      - `kelola.blade.php`
      - `form.blade.php`
      - `detail.blade.php`
  - `dashboard/`
    - `index.blade.php`
  - `qa/`
    - `inspections.blade.php`
    - `detail-approval.blade.php`
  - `user/`
    - `list.blade.php`

## routes/
- `web.php`
- `ums.php`
- `console.php`

## storage/
- `app/` (`.gitignore`)
  - `public/` (`.gitignore`)
  - `private/` (`.gitignore`)
- `framework/`
  - `cache/` (`.gitignore`)
  - `sessions/` (`.gitignore`)
  - `views/` (`.gitignore`)
  - `testing/` (`.gitignore`)
- `logs/` (`.gitignore`)

## tests/
- `TestCase.php`
- `Feature/`
  - `ExampleTest.php`
- `Unit/`
  - `ExampleTest.php`

## vendor/
- (dependency packages — not expanded here)

---

## Notes & Findings
- PHP syntax scan (`php -l`) returned no syntax errors in project PHP files.
- `vite.config.js` imports `@tailwindcss/vite` and calls `tailwindcss()` — this package name is not a commonly used official plugin. If not installed, Vite will error. Typical Tailwind usage with Laravel + Vite uses `tailwindcss` via PostCSS (see `postcss.config.js`) or a recognized Vite plugin. Verify `package.json` and installed packages, or remove the plugin call and rely on PostCSS.
- There are UMS blade views and related controllers/routes for `admin_scopes` — ensure database migrations and routes are applied before enabling related links.

## Next steps (suggested)
- If you want, I can update `vite.config.js` to remove the unknown Tailwind plugin and add a short note, or run `npm install` and `npm run dev` to reproduce any Vite errors (requires Node installed locally).
- I can also expand `vendor/` package list if needed.

---

If you want the file named differently (e.g. `PROJECT_STRUCTURE.md`), or want me to commit this file to git, tell me and I'll update accordingly.
