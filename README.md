# RADIANT SOLAR SOLUTIONS — Solar CRM & ERP

A full-featured **Laravel 9** web application for managing solar energy business operations — from lead generation and sales quotations to installation, inventory (ERP), payments, and subsidy claims. Comes with a companion **mobile app API** and a **role-based admin panel**.

## Important Note for AI / LLM Tools

> **Note:** If the database already contains tables, use `php artisan migrate --path=database/migrations/<new_migration_file>` to run only new migrations.

> **⚠️ AI / Developer Note:** Do NOT rely on migration files for understanding the database schema — they are incomplete and outdated. Always use **`database/_actual_schema.sql`** as the single source of truth for the live database structure. This file contains all CREATE TABLE definitions extracted directly from the production database.
>
> **Updating the schema file** — whenever tables are added or modified, regenerate it with:
> ```bash
> mysqldump -u root -proot --no-data --skip-comments --skip-add-drop-table admin_radiant > database/_actual_schema.sql
> ```

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 9, PHP 8.0+ |
| **Database** | MySQL (`admin_radiant`) |
| **Auth** | Laravel Fortify (web), Laravel Sanctum (API tokens) |
| **Roles & Permissions** | Spatie `laravel-permission` |
| **PDF** | Barryvdh/DomPDF |
| **Excel** | Maatwebsite/Laravel-Excel |
| **Data Tables** | Yajra Laravel DataTables |
| **Image Handling** | Intervention Image |
| **Frontend** | Blade Templates + Laravel UI (Bootstrap) |
| **Build Tool** | Vite |

## Modules

### 1. Lead Management
- Lead capture from multiple sources (IndiaMART API, manual entry)
- Lead status tracking, follow-ups with image attachments
- Import/Export leads via Excel

### 2. Sales Quotation
- Create quotations with panel/inverter selection, BOM, rate calculator
- PDF generation, status tracking (Active / Accepted / Revised / Cancelled-Lost)
- Technical specifications management

### 3. Sales Order
- Complete lifecycle status flow:
  `Application Pending → Pending Approval → Feasibility Approved → Meter Charge Paid → Payment Received → Dispatch Pending List → Installation Pending → Installation Done → Meter Application Done → Meter Installation → Subsidy Request → Subsidy Disbursal`
- Loan branch (after Meter Charge Paid): `Apply for Loan / Login → Loan Sanction → Disbursement`, then continues to `Payment Received`
- Subsidy steps are skipped for GEDA/National registrations where the subsidy is given up
- Terminal states: `Hold / Query`, `File Cancel Order`
- PDF document generation (agreements, self-certification, GEDA, net metering, request letters, etc.)
- Status change logging with full audit trail


### 5. Payment Collection
- Payment tracking linked to sales orders
- Payment status management (Approved / Pending / Hold / Return)
- Payment & pending-payment reports with Excel export
- Disbursement tracking for loan orders

### 6. Installation Management
- Installation records with panel and inverter mapping
- Serial number tracking for panels
- Image uploads (installation, panel, inverter photos)
- Dispatch planning with installer assignment

### 7. ERP / Inventory
- **Warehouse Stock** management
- **Purchase Orders** (with receive tracking) & **Purchase Direct**
- **Delivery Challans** (issue & return) with serial number tracking
- **Project-wise Stock** management
- **Stock Adjustments** (warehouse & project)
- **BOM (Bill of Materials)** management
- **Supplier Management**
- **Rate Calculator**

### 8. Reports
30+ report types with Excel export:
- Sales Order, Payment, Collection, Pending Payment
- Dispatch, Installation, Meter Application, Meter Charges
- Invoice, Subsidy Claim, Final Orders
- Stock Reports, B2B Dispatch, Project-wise reports
- Panels/Inverters Required, Serial Number tracking

### 9. User Management
- Role-based access (Owner, Manager, Sales, Installer, Site Visitor)
- Granular permissions via Spatie
- Agent / Sales Person management

### 10. Master Data
- States, Cities, Districts, Talukas, Villages
- Categories, Products, Units, Sources
- Panel Companies / Types / Wattages
- Inverter Companies
- Banks, Policies, DISCOMs, Sub-Divisions
- Financial Years

### 11. Mobile API
RESTful API (Sanctum auth) for mobile app covering:
- Auth (login, register, OTP, password reset)
- Leads, Follow-ups, Estimates, Tasks
- Sales orders, Quotations, Payments
- Installations, Stock lookups
- Delivery Challans, Purchase Direct

### 12. Notifications & Messaging
- In-app notifications
- Messaging between users
- Email notifications (OTP, admin alerts)

## Installation

```bash
# 1. Clone the repository
git clone <repo-url> radiant_solar
cd radiant_solar

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install && npm run build

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_radiant
DB_USERNAME=root
DB_PASSWORD="root"

# 6. Run migrations
php artisan migrate

# 7. Seed data
php artisan db:seed

# 8. Serve
php artisan serve
```

## Key Configuration (`.env`)

| Variable | Description |
|---|---|
| `APP_NAME` | RADIANT SOLAR SOLUTIONS |
| `PER` | Panel efficiency percentage (default: 8.9) |
| `APP_OWNER_NAME` | Business owner name (Rebin K Kansagra) |
| `APP_ELECTRICAL_CONTRACTOR` | Contractor name for documents |
| `APP_ELECTRICAL_LICENSE_NO` | License number for PDFs |
| `APP_SORT` | Sorting prefix (RSS) |
| `APP_EMPANELMENT` | Empanelment number (NP-051) |
| `RECAPTCHA_SITE_KEY` | Google reCAPTCHA site key |
| `RECAPTCHA_SECRET_KEY` | Google reCAPTCHA secret key |

## Project Structure

```
app/
├── Actions/Fortify/       # Fortify actions
├── Exports/               # Excel export classes
├── Helper/                # Helper functions
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         # Admin controllers
│   │   ├── Api/           # Mobile API controllers
│   │   ├── Auth/          # Auth controllers
│   │   ├── erp/           # ERP controllers
│   │   └── Manager/       # Manager controllers
│   └── Middleware/
├── Imports/               # Excel import classes
├── Mail/                  # Mailables
├── Models/                # Eloquent models
├── Providers/             # Service providers
└── Services/              # Role/Permission/User services
resources/views/           # Blade templates
routes/
├── web.php                # Web routes
├── api.php                # API routes
└── console.php            # Artisan commands
database/migrations/       # 79+ migration files
config/                    # Laravel config files
```


## License

Proprietary — RADIANT SOLAR SOLUTIONS
