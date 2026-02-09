# PracticeRx AI Coding Assistant Instructions

## Project Overview
PracticeRx is an **all-in-one WordPress practice management system** for health professionals including:
- Mental Health Professionals
- Functional Medicine Practitioners
- Health Coaches
- Medical Doctors
- Nutritionists & Dietitians
- Personal Trainers
- Nurse Practitioners
- Naturopathic Doctors
- Chiropractors

**Core Features:**
- **Client Management** (not "patients" - use "client" for frontend users)
- **Appointment Scheduling & Calendar**
- **Treatment Programs/Packages** (sellable programs with sessions)
- **Forms Builder** (intake forms, health questionnaires with conditional logic)
- **Document Library** (secure file sharing with clients)
- **Health Tracking** (vitals, labs, progress charts)
- **Client Portal** (branded client-facing interface)
- **Encounter Reports** (detailed practitioner forms with digital signatures)
- **Billing & Invoicing** (payment processing, refunds)
- **Analytics & Reports** (revenue, client growth, practitioner performance)
- **SMS Notifications** (Twilio integration)
- **Email Notifications** (automated templates)
- **Payment Gateway Integration** (Stripe, WooCommerce)
- **Telehealth Integration** (Zoom/Twilio Video conferencing)
- **Drip Email Campaigns** (automated client engagement)
- **Meal Planning** (recipes, meal plans, client nutrition assignments)

**Tech Stack:**
- Backend: WordPress plugin architecture, custom database tables, REST API, PSR-4 autoloading
- Frontend: React (via `@wordpress/element`), custom hash-based router
- Build: `@wordpress/scripts` (webpack-based)
- Database: Custom tables with `ppms_` prefix

## Architecture

### Backend Structure (PHP - PSR-4)

**Namespace Organization:**
- `PracticeRx\` → `includes/` (main namespace)
  - `includes/Api/` → API Controllers (REST endpoints)
  - `includes/Models/` → Data models (Active Record pattern)
  - `includes/Services/` → Business logic layer
  - `includes/Core/` → Core functionality (activation, admin pages)
  - `includes/Auth/` → Authentication & authorization
  - `includes/Database/` → Schema definitions

**Key Classes & Patterns:**
- **Base Classes:**
  - `AbstractModel` → Base for all data models (active record pattern)
  - `ApiController` → Base for REST API controllers
  - Custom tables use `ppms_` prefix (e.g., `wp_ppms_clients`, `wp_ppms_appointments`)

- **Models:** Extend `AbstractModel` with static methods
  ```php
  Client::get($id)                    // Get by ID
  Client::create($data)               // Create new record
  Client::get_by_user_id($user_id)   // Custom query method
  Client::update($id, $data)          // Update existing
  Client::delete($id)                 // Delete record
  Client::search($term)               // Search clients
  ```

- **API Controllers:** Extend `ApiController`, namespace `ppms/v1`
  - Routes registered in `practicerx.php` `init_rest_api()`
  - Permission checks via `check_permissions()` method
  - Return `WP_Error` for errors, not exceptions

- **Services Layer:** Business logic separation
  - `BillingService` → Payment processing, invoices, refunds
  - `AppointmentService` → Appointment management, availability
  - `SMSService` → SMS notifications via Twilio
  - `EmailService` → Email notifications with templates
  - `ReportsService` → Analytics and reporting
  - `TelehealthService` → Video conferencing (Zoom/Twilio Video)
  - `CampaignService` → Drip email campaign automation

**25 Database Tables:**
- `ppms_clients` - Client records (renamed from patients for inclusivity)
- `ppms_patients` - Legacy table (use clients for new code)
- `ppms_practitioners` - Healthcare provider records
- `ppms_appointments` - Appointment scheduling
- `ppms_encounters` - Session/visit records
- `ppms_encounter_reports` - Detailed clinical notes with signatures
- `ppms_services` - Billable services catalog
- `ppms_invoices` - Billing invoices
- `ppms_payments` - Payment transactions
- `ppms_programs` - Treatment packages/programs (sellable)
- `ppms_client_programs` - Program enrollments
- `ppms_forms` - Form builder definitions
- `ppms_form_submissions` - Client form responses
- `ppms_documents` - Document library
- `ppms_health_metrics` - Vitals, labs, measurements
- `ppms_portal_settings` - Client portal customization
- `ppms_sms_logs` - SMS notification history
- `ppms_email_logs` - Email notification history
- `ppms_telehealth_sessions` - Video session records
- `ppms_email_campaigns` - Drip email campaign definitions
- `ppms_campaign_subscribers` - Campaign subscriptions
- `ppms_meal_plans` - Meal plan templates
- `ppms_client_meal_plans` - Client meal plan assignments
- `ppms_recipes` - Recipe library

**Role & Permission System:**
- Custom capabilities: `ppms_practitioner`, `ppms_patient`
- Role checks: `RoleManager::is_practitioner()`, `RoleManager::is_patient()`
- Permissions verified in API controllers via `check_permissions()` callback
- Default permission: `current_user_can('read')`

### Database Schema Management

**Schema Definition:** [includes/Database/Schema.php](../includes/Database/Schema.php)
- All tables use `ppms_` prefix (Private Practice Management System)
- Created via `Schema::create_tables()` using `dbDelta()`
- Executed during plugin activation in `Installer::install()`

**Table Structure Pattern:**
```php
CREATE TABLE {$wpdb->prefix}ppms_tablename (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    field1 varchar(255) DEFAULT '',
    field2 text DEFAULT '',
    created_at datetime DEFAULT '0000-00-00 00:00:00',
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY field1 (field1)
) $charset_collate;
```

**Schema Updates:**
1. Modify table SQL in `Schema::create_tables()`
2. Use `dbDelta()` for safe updates (adds columns, doesn't drop)
3. For column alterations, use `maybe_add_column()` helper with option flags
4. Run via plugin reactivation or programmatically call `Installer::install()`

### Frontend Structure (React)
- **Entry point:** [src/index.js](../src/index.js) renders into `#practicerx-root` div
- **Routing:** Custom hash-based router in [src/utils/Router.jsx](../src/utils/Router.jsx)
  - Navigation: `window.location.hash = newPath` or `<Link to="/path">`
  - Routing logic is manual switch statement in [src/index.js](../src/index.js)
- **API calls:** Use `@wordpress/api-fetch` with paths like `/ppms/v1/patients`
- **Layout:** [src/components/Layout.jsx](../src/components/Layout.jsx) provides sidebar + content wrapper

**Key Patterns:**
- Use `@wordpress/element` instead of direct React imports for hooks
- API root and nonce injected via `wp_localize_script` as `practicerxSettings`
- Inline styles used throughout (no external CSS framework)

### Payment Gateway System
- **Interface:** `PaymentGatewayInterface` defines contract
- **Factory:** `GatewayFactory::get($gateway_id)` returns gateway instances
- **Gateways:** Extend `AbstractGateway` (Stripe, WooCommerce implemented)
- Add new gateways by implementing interface + updating factory switch

**Gateway Implementation Pattern:**
```php
// 1. Implement interface
class NewGateway implements PaymentGatewayInterface {
    public function get_id() { return 'gateway_id'; }
    public function get_title() { return 'Gateway Name'; }
    public function process_payment($amount, $currency, $customer, $reference) { /* logic */ }
    public function verify_transaction($transaction_id) { /* logic */ }
}

// 2. Register in GatewayFactory::get()
case 'gateway_id':
    return new NewGateway();
```

## Feature Modules

### Telehealth Integration
- **Service:** `TelehealthService` - Multi-provider video conferencing
- **Providers:** Zoom (JWT authentication), Twilio Video (Room API)
- **Model:** `TelehealthSession` - Session records with meeting URLs
- **Controller:** `TelehealthController` - REST API for session management
- **Features:**
  - Create video meetings (Zoom meetings or Twilio rooms)
  - Generate access tokens and join URLs
  - Track session status (scheduled, in-progress, completed)
  - End sessions and retrieve recordings
  - Email notifications with meeting links
- **Configuration:** Set `ppms_telehealth_provider`, `ppms_zoom_api_key`, `ppms_twilio_account_sid`

### Drip Email Campaigns
- **Service:** `CampaignService` - Automated email campaign processing
- **Models:** `EmailCampaign`, `CampaignSubscriber`
- **Controller:** `CampaignsController` - Campaign and subscription management
- **Features:**
  - Create multi-step email campaigns with delays
  - Subscribe/unsubscribe clients to campaigns
  - Merge tags for personalization ({{first_name}}, {{last_name}}, etc.)
  - Event-triggered campaigns (appointment booked, program enrolled)
  - Cron-based processing via `process_campaigns()`
  - Track subscriber progress through campaign steps
- **Triggers:** Manual, appointment_booked, program_enrolled, form_submitted

### Meal Planning
- **Models:** `Recipe`, `MealPlan`, `ClientMealPlan`
- **Controllers:** `RecipesController`, `MealPlansController`, `ClientMealPlansController`
- **Features:**
  - Recipe library with ingredients, instructions, macros
  - Public/private recipes for sharing
  - Meal plan templates (breakfast, lunch, dinner, snacks)
  - Assign meal plans to clients with customizations
  - Track calories, protein, carbs, fats
  - Search by meal type, tags, ingredients
  - Duration-based plans with start/end dates
- **Recipe Fields:** title, description, meal_type, prep_time, cook_time, servings, macros, tags
- **Meal Plan Fields:** meals (JSON day structure), macros (JSON targets), duration_days, is_template

## Development Workflow

### Building
```bash
# Development with hot reload
npm start

# Production build (outputs to build/)
npm run build

# Regenerate autoloader after adding new classes
composer dump-autoload
```
Build outputs: `build/index.js` and `build/index.asset.php` (dependencies manifest)

### Database Changes
1. Create new migration file in `includes/Database/Migrations/`
2. Use `ppms_maybe_create_table()` for new tables or `ppms_maybe_add_column()` for columns
3. Run via plugin reactivation or `Installer::install()`
4. Migration files are auto-loaded by `Schema::create_tables()`

### Adding REST Endpoints
1. Create controller in `includes/Api/` extending `ApiController`
2. Register routes in `register_routes()` method
3. Add controller instantiation in [practicerx.php](../practicerx.php) `init_rest_api()`

### Adding Models
1. Create class in `includes/Models/` extending `AbstractModel`
2. Set `protected static $table = 'ppms_tablename'` (without wp prefix)
3. Models auto-handle timestamps (`created_at`, `updated_at`)

**Model Pattern:**
```php
namespace PracticeRx\Models;

class Invoice extends AbstractModel {
    protected static $table = 'ppms_invoices';
    
    // Add custom query methods
    public static function get_by_patient($patient_id) {
        global $wpdb;
        $table = self::get_table();
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE patient_id = %d", $patient_id)
        );
    }
}
```

### Adding Frontend Pages
1. Create page component in `src/pages/`
2. Add route in [src/index.js](../src/index.js) switch statement
3. Add navigation link in [src/components/Layout.jsx](../src/components/Layout.jsx) sidebar

### Adding Filter Classes
1. Create filter class in `includes/Filters/` extending base or standalone
2. Register hooks in `__construct()` method
3. Files are auto-loaded by `FilterHandler::init()`
4. Follow naming pattern: `{Feature}Filters.php`

## Critical Conventions

**PHP:**
- Use WordPress coding standards (tabs, Yoda conditions)
- Always escape output: `esc_html()`, `esc_attr()`, `esc_url_raw()`
- Prepare SQL queries: `$wpdb->prepare()`
- Return `WP_Error` objects for errors, not exceptions
- Text domain: `'practicerx'` for all `__()` calls

**JavaScript:**
- Use functional components with hooks
- Import hooks from `@wordpress/element`: `import { useState } from '@wordpress/element'`
- Use `apiFetch` for API calls, not fetch/axios
- Handle loading states and errors in UI

**Dppms_maybe_create_table($table_name, $sql)` → Create table if doesn't exist
- `ppms_maybe_add_column($table_name, $column_name, $sql)` → Add column if doesn't exist
- `dbDelta()` → WordPress schema update function (safe for existing tables)
- Use `$wpdb->get_charset_collate()` for charset/collation

**Helper Functions:**
- `ppms_get_option($name)` / `ppms_update_option($name, $value)` → Plugin options
- `ppms_is_practitioner()` / `ppms_is_patient()` → Role checks
- `ppms_user_can($capability)` → Permission checks
- `ppms_format_currency($amount, $currency)` → Currency formatting
- `ppms_log($message, $context)` → Debug logging
- See [includes/helpers.php](../includes/helpers.php) for full list
- **Autoloading:** PSR-4 via Composer + custom autoloader in [practicerx.php](../practicerx.php)
- Admin page hook: `toplevel_page_practicerx` ([includes/Core/AdminPage.php](../includes/Core/AdminPage.php))
- **Database:** `includes/Database/Migrations/` for modular table definitions
- **Filters:** `includes/Filters/` for feature-based filter classes (auto-loaded)
- **Dual src structure:** Both `assets/js/` and `src/` exist. Use `src/` for new React code
- **Build artifacts:** `build/` directory is generated, do not edit manually
- **Autoloading:** PSR-4 via Composer + custom autoloader in [practicerx.php](../practicerx.php)
- **Global helpers:** `includes/helpers.php` auto-loaded via Composer
- `dbDelta()` → WordPress schema update function (safe for existing tables)
- Use `$wpdb->get_charset_collate()` for charset/collation

## File Organization

- **Dual src structure:** Both `assets/js/` and `src/` exist. Use `src/` for new React code
- **Build artifacts:** `build/` directory is generated, do not edit manually
- **Autoloading:** PSR-4 via custom autoloader in [practicerx.php](../practicerx.php)

## Integration Points

**WordPress Integration:**
- Admin page hook: `toplevel_page_practicerx` ([includes/Core/AdminPage.php](../includes/Core/AdminPage.php))
- Assets enqueued only on PracticeRx admin page
- REST API namespace: `ppms/v1`
- Capabilities: `ppms_practitioner`, `ppms_patient`

**Data Flow:**
React Components → `apiFetch` → WordPress REST API (`/wp-json/ppms/v1/*`) → Controllers → Models → Database

## Common Development Tasks

**Add new feature module (e.g., Program Management):**
1. Create migration in `includes/Database/Migrations/ppms-programs-db.php`
2. Add table constant to `includes/Core/Constants.php`
3. Create `includes/Models/Program.php` extending `AbstractModel`
4. Create `includes/Api/ProgramsController.php` with CRUD routes
5. Register controller in `practicerx.php` `init_rest_api()` method
6. Create React components in `src/components/` or `src/pages/`
7. Add granular capabilities to `Constants.php` and `Installer.php`

**Add feature filter:**
1. Create `includes/Filters/{Feature}Filters.php`
2. Define hooks in `__construct()` method
3. Auto-loaded by `FilterHandler` on initialization

**Add payment gateway:**
1. Implement `PaymentGatewayInterface` in `includes/Services/Gateways/`
2. Optionally extend `AbstractGateway` for common methods
3. Add case to `GatewayFactory::get()` and `get_all()`

**Add helper function:**
1. Add to `includes/helpers.php` with `ppms_` prefix
2. Use throughout plugin for consistency
3. Consider adding to `Helper` class for OOP access

## Improvements Over KiviCare

PracticeRx is designed to be lighter and more maintainable than KiviCare:

**1. Centralized Constants**
- Single source of truth for table names, capabilities, and statuses
- Type-safe access via Constants class
- Easy to maintain and update

**2. Modular Database Migrations**
- Individual migration files for each table
- Easy to add/modify tables without touching core Schema class
- Better version control and code review

**3. Global Helper Functions**
- Lightweight utility functions with `ppms_` prefix
- Quick access without class instantiation
- Combines best of procedural and OOP

**4. Auto-loading Filter System**
- Feature-based organization
- Automatic discovery and initialization
- Reduces boilerplate in main plugin file

**5. Enhanced Model Capabilities**
- Built-in query methods: `find_by()`, `count()`, pagination support
- Consistent API across all models
- Less boilerplate in custom queries

**6. Granular Permission System**
- Per-action capabilities (add, edit, delete, view, list)
- Easy to extend and customize
- Role-based access control built-in
**Add payment gateway:**
1. Implement `PaymentGatewayInterface` in `includes/Services/Gateways/`
2. Optionally extend `AbstractGateway` for common methods
3. Add case to `GatewayFactory::get()` and `get_all()`

## Inspired By

This codebase follows patterns from KiviCare Pro clinic management system:
- **Filter-based architecture:** Features organized as filters/hooks for modularity
- **Base classes:** Common functionality in base classes (KCBase → AbstractModel)
- **Helper utilities:** Global helper functions in `utils/` for common operations
- **Namespace pattern:** PSR-4 autoloading with clear namespace-to-folder mapping
- **Database migrations:** Separate migration files in `database/` folder
- **Option management:** Plugin-prefixed options (`ppms_` prefix for all options)
- **Permission per action:** Granular permissions mapped to specific functions/actions
