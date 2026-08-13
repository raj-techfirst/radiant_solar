# Radiant Solar Solutions — Solar ERP + CRM

A comprehensive Solar Energy Enterprise Resource Planning and Customer Relationship Management system built with Laravel 9. Manages the full lifecycle of solar installations — from lead generation and sales quotations through installation, commissioning, project completion, and post-sales support.

Supports both **Physical Subsidy (GEDA / National portal)** and **Loan (C/L file type)** workflows, with separate **old** and **new** installation schemes.

## Features

### CRM
- **Lead Management** — Import, track, and convert leads; lead source tracking; lead won/completion workflow
- **Follow-ups** — Schedule and log follow-up activities per lead
- **Estimates** — Generate customer estimates with itemized pricing; PDF export
- **Sales Quotations** — Create quotations with panel/watt selection; status workflow; PDF generation
- **Inquiry Management** — Public inquiry form with admin follow-up tracking and dedicated inquiry dashboard
- **Task Management** — Assign and track tasks for team members

### Sales & Order Management
- **Sales Orders** — Full order lifecycle from creation to project completion; multi-step status workflow (Application Pending → Pending Approval → Feasibility → Meter Charge → Dispatch → Installation → Meter Application → Meter Installation → Subsidy Request → Subsidy Disbursal → **Project Completion**)
- **Loan Workflow** — Loan file types (C = cash / L = loan) with Apply for Loan → Loan Sanction → Disbursement statuses
- **Payment Collection** — Record payments against sales orders; multiple payment statuses
- **Commission Management** — Agent / salesperson commission calculation (Meter Charge + Installation slabs), payment tracking, and downloadable reports
- **Status Timeline** — Visual vertical timeline of the full status flow in the sales order detail modal, with remove-status support

### Installation Management
- **Installation Wizard** — Multi-step form covering panel/inverter selection along with old (`form_type='old'`) and new (`form_type='new'`) installation schemes
- **BOM Integration** — Bill of Materials linked to installations for stock consumption
- **Document Upload** — Upload site images, panel/inverter images, generation meter photos
- **Installation Item Allocation** — Link cable/structure items (from Item List) with `use_stock` quantities consumed per installation

### ERP (Inventory & Supply Chain)
- **Products & Item Groups** — Manage product catalog with grouping (panel / inverter / cable / structure) and serial number tracking
- **Warehouse Management** — Multi-warehouse stock tracking with adjustments and warehouse From/To transfers
- **Purchase Orders** — Create and receive purchase orders
- **Purchase Direct** — Direct purchase entries with serial number import
- **Delivery Challans** — Create delivery challans with return management
- **Project-Wise Stock** — Track stock allocated to specific projects/sites
- **BOM (Bill of Materials)** — Define component structures for rate calculation and stock forecasting
- **Rate Calculator** — Calculate project costs based on BOM data
- **Stock Reports** — Available stock, required stock, serial number tracking

### Reports & Exports
- Payment Collection Report
- Sales Order Report (Sales Order + Sales Order With Details)
- Payment Pending Report
- Meter Charges Report
- Dispatch Report
- Installation Report (Old) & Installation Report (New — dynamic cable/structure columns)
- Meter Application Report
- Final Orders Report (Project Completion only)
- Invoice Report
- Panels Required / Inverters Required Reports
- Subsidy Claim Report
- B2B Dispatch Report
- Project-Wise Stock & Dispatch Reports
- Commission Details / Commission List / Commission Files Export
- 30 Excel export classes

### Document Generation (PDF)
- Self-Certification PDF (signed / unsigned)
- Request Letter PDF
- Model Agreement PDF (signed / unsigned)
- GEDA Agreement PDF
- PMSGMBY Commissioning PDF
- Declaration DCR PDF
- Agreement PDF (signed / unsigned)
- Rajasthan-specific PDFs — Net Metering Interconnection Agreement, Net Meter PDF, Vendor Feasibility PDF

### Mobile API
- Full REST API with Sanctum authentication
- Role-based API access (Owner, Manager, Sales, Installer, Site Visitors, Accountant, Office, Super Admin)
- Mobile endpoints for leads, sales, estimates, tasks, installations, payments, stock, and ERP operations

### Role-Based Access Control
- Spatie Laravel-Permission for roles and permissions
- Roles: Owner, Manager, Sales, Installer, Site Visitors, Accountant, Office, Super Admin
- Granular permission management via web UI

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 8.x / Laravel 9.x |
| **Frontend** | Blade templates, Bootstrap 5, jQuery, Vite |
| **Database** | MySQL / MariaDB |
| **Auth** | Laravel Sanctum (API), Laravel Fortify / Laravel UI (Web) |
| **RBAC** | Spatie Laravel-Permission |
| **PDF** | Barryvdh DomPDF |
| **Excel** | Maatwebsite Laravel Excel |
| **DataTables** | Yajra Laravel DataTables |
| **Image Processing** | Intervention Image |
| **HTTP Client** | GuzzleHTTP |

## Requirements

- PHP 8.x
- Composer
- MySQL 5.7+ / MariaDB
- Node.js & NPM (for Vite asset compilation)

## Installation

```bash
# Clone the repository
git clone <repository-url> radiant-solar
cd radiant-solar

# Install PHP dependencies
composer install

# Install and build frontend assets
npm install
npm run build

# Environment configuration
cp .env.example .env
php artisan key:generate

# Edit .env with your database credentials
# DB_DATABASE, DB_USERNAME, DB_PASSWORD

# Run migrations (use --path if migrating on an existing database)
php artisan migrate

# Seed roles and permissions
php artisan db:seed

# Storage link
php artisan storage:link

# Serve the application
php artisan serve
```

> **Note:** If the database already contains tables, use `php artisan migrate --path=database/migrations/<new_migration_file>` to run only new migrations.

> **⚠️ AI / Developer Note:** Do NOT rely on migration files for understanding the database schema — they are incomplete and outdated. Always use **`database/_actual_schema.sql`** as the single source of truth for the live database structure. This file contains all CREATE TABLE definitions extracted directly from the production database.
>
> **Updating the schema file** — whenever tables are added or modified, regenerate it with:
> ```bash
> mysqldump -u root -proot --no-data --skip-comments --skip-add-drop-table admin_radiant > database/_actual_schema.sql
> ```

## Configuration

Key `.env` variables:

```
APP_NAME="RADIANT SOLAR SOLUTIONS"
APP_URL=https://radiant.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_radiant
DB_USERNAME=root
DB_PASSWORD=root

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_FROM_ADDRESS=noreply@radiantsolar.com
```

## Project Structure

```
app/
├── Console/             # Artisan commands
├── Exports/             # 30 Excel export classes
├── Helper/              # Global helper functions (helper.php)
├── Http/
│   ├── Controllers/     # 85 controllers (Web, API, Admin, ERP, Auth, Manager)
│   └── Middleware/      # Custom middleware
├── Imports/             # Excel import classes
├── Mail/                # Mail classes (OTP, admin notify)
├── Models/              # 79 Eloquent models (60 root + 19 ERP)
└── Services/            # Permission, Role, User services

config/
├── app.php
├── permission.php       # Spatie permissions configuration
└── ...

database/
├── _actual_schema.sql   # Live database schema (CREATE TABLE only) — use this instead of migrations for accurate DB structure
├── migrations/          # 80 migration files
└── seeders/             # Database seeders

resources/
├── views/
│   ├── admin/           # Admin panel views (sale, installation, etc.)
│   ├── erp/             # ERP module views
│   ├── layouts/         # Layout templates
│   ├── auth/            # Authentication views
│   └── ...

routes/
├── web.php              # Web routes (210+ route definitions)
└── api.php              # Sanctum-authenticated API routes (121 endpoints)

public/
└── uploads/             # User-uploaded images
    ├── installation/
    ├── invater/
    ├── penal/
    ├── payment_collections/
    ├── reparing/
    └── site_visit_images/
```

## Sales Order Status Flow

Application Pending → Pending Approval → Feasibility Approved → Meter Charge Paid → (Payment Received) → Dispatch Pending List → Installation Pending → Installation Done → Meter Application Done → Meter Installation → Subsidy Request → Subsidy Disbursal → **Project Completion**

- **Project Completion** — subsidy-eligible files require Subsidy Disbursal complete before Project Completion; non-subsidy files (GEDA or National with subsidy give-up) can be marked complete after Meter Installation. A date is captured when the status is changed.

## License

Proprietary — Radiant Solar Solutions