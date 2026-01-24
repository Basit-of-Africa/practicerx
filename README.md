# PracticeRx - Private Practice Management System

A lightweight, modern WordPress plugin for managing private medical practices. Built with enterprise-grade architecture inspired by KiviCare, but optimized for performance and maintainability.

## Features

- **Patient Management** - Comprehensive patient records with medical history
- **Appointment Scheduling** - Flexible appointment booking with practitioner availability
- **Encounter Notes** - Clinical documentation and patient encounters
- **Billing & Payments** - Invoice generation with multiple payment gateways
- **Role-Based Access** - Granular permissions for practitioners and patients
- **React SPA Frontend** - Modern, fast user interface
- **Modular Architecture** - Filter-based extensibility

## Tech Stack

- **Backend:** PHP 7.4+, WordPress 5.8+
- **Frontend:** React 18, WordPress Components
- **Build:** @wordpress/scripts (webpack)
- **Database:** MySQL with custom tables
- **Architecture:** PSR-4 autoloading, Filter-based plugins

## Installation

1. Upload plugin to `/wp-content/plugins/practicerx/`
2. Run `composer install` in plugin directory
3. Activate plugin through WordPress admin
4. Tables and roles will be created automatically

## Development

```bash
# Install dependencies
npm install
composer install

# Start development server
npm start

# Build for production
npm run build

# Regenerate autoloader after adding classes
composer dump-autoload
```

## Architecture Overview

### Backend Structure
```
includes/
├── Api/              # REST API controllers
├── Auth/             # Authentication & roles
├── Core/             # Core classes (Constants, Helper, FilterHandler)
├── Database/
│   └── Migrations/   # Modular table definitions
├── Filters/          # Feature-based filter classes
├── Models/           # Data models (Active Record)
├── Services/         # Business logic layer
└── helpers.php       # Global utility functions
```

### Frontend Structure
```
src/
├── components/       # Reusable React components
├── pages/            # Page components
├── utils/            # Utilities (Router, etc.)
└── index.js          # App entry point
```

## Key Improvements Over KiviCare

### 1. Centralized Configuration
- `Constants.php` - Single source for table names, capabilities, statuses
- Type-safe access throughout the codebase
- Easy to maintain and extend

### 2. Modular Database Migrations
- Individual migration files per table
- Auto-loaded by Schema class
- Version control friendly

### 3. Global Helper Functions
- Lightweight `ppms_*()` functions for common tasks
- No class instantiation overhead
- Best of procedural and OOP patterns

### 4. Auto-loading Filter System
- Feature-based organization in `includes/Filters/`
- Automatic discovery and initialization
- Reduced boilerplate

### 5. Enhanced Models
- Built-in query methods: `find_by()`, `count()`, pagination
- Consistent API across all models
- Less custom SQL needed

### 6. Granular Permissions
- Per-action capabilities (add, edit, delete, view, list)
- Easy role customization
- Built-in permission checks

## Adding New Features

### Add a New Entity
```php
// 1. Create migration file
// includes/Database/Migrations/ppms-my_entity-db.php
<?php
global $wpdb;
$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'ppms_my_entity';
$sql = "CREATE TABLE {$table_name} (...)";
ppms_maybe_create_table( $table_name, $sql );

// 2. Add to Constants.php
const TABLE_MY_ENTITY = 'ppms_my_entity';

// 3. Create Model
class MyEntity extends AbstractModel {
    protected static $table = 'ppms_my_entity';
}

// 4. Create API Controller
class MyEntityController extends ApiController {
    // Register routes...
}

// 5. Register in practicerx.php
$controllers = array(
    // ... existing controllers
    new \PracticeRx\Api\MyEntityController(),
);
```

### Add a Filter Class
```php
// includes/Filters/MyFeatureFilters.php
namespace PracticeRx\Filters;

class MyFeatureFilters {
    public function __construct() {
        add_filter( 'ppms_my_filter', array( $this, 'my_callback' ) );
    }
    
    public function my_callback( $data ) {
        // Filter logic
        return $data;
    }
}
```

## Helper Functions

```php
// Options
ppms_get_option( 'setting_name' );
ppms_update_option( 'setting_name', $value );

// Role checks
ppms_is_practitioner();
ppms_is_patient();
ppms_user_can( Constants::CAP_PATIENT_ADD );

// Formatting
ppms_format_currency( 100.50, 'USD' );
ppms_format_datetime( '2026-01-24 14:30:00' );

// Database
ppms_get_table( Constants::TABLE_PATIENTS );
ppms_maybe_create_table( $table_name, $sql );
ppms_maybe_add_column( $table_name, $column_name, $sql );

// Debugging
ppms_log( 'Debug message', 'context' );
```

## REST API Endpoints

```
GET    /wp-json/ppms/v1/patients
GET    /wp-json/ppms/v1/patients/:id
POST   /wp-json/ppms/v1/patients
PUT    /wp-json/ppms/v1/patients/:id
DELETE /wp-json/ppms/v1/patients/:id

GET    /wp-json/ppms/v1/appointments
GET    /wp-json/ppms/v1/appointments/:id
POST   /wp-json/ppms/v1/appointments
...
```

## Permissions

### Practitioner Capabilities
- `ppms_patient_list`, `ppms_patient_add`, `ppms_patient_edit`, `ppms_patient_view`
- `ppms_appointment_list`, `ppms_appointment_add`, `ppms_appointment_edit`, `ppms_appointment_delete`
- `ppms_encounter_list`, `ppms_encounter_add`, `ppms_encounter_edit`, `ppms_encounter_view`
- `ppms_invoice_list`, `ppms_invoice_add`, `ppms_invoice_edit`, `ppms_invoice_view`

### Patient Capabilities
- `ppms_view_own_appointments`, `ppms_book_appointments`
- `ppms_view_own_encounters`, `ppms_view_own_invoices`
- `ppms_pay_invoices`

## Contributing

See [.github/copilot-instructions.md](.github/copilot-instructions.md) for detailed architecture and coding guidelines.

## License

GPL-2.0-or-later

## Credits

Inspired by KiviCare Pro clinic management system, reimagined for modern WordPress development.
