# ERP API — Distribution & Sales Management Backend

![PHP](https://img.shields.io/badge/PHP-7.3%20%7C%208.x-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-8.x-FF2D20?logo=laravel&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-4479A1?logo=mysql&logoColor=white)
![Passport](https://img.shields.io/badge/Auth-Laravel%20Passport-blue)
![WebSockets](https://img.shields.io/badge/Realtime-WebSockets-green)

A RESTful API built with **Laravel 8** that powers an **ERP (Enterprise Resource Planning)** application for a distribution/trading business. It covers the entire business cycle: customer orders, purchasing from suppliers, sales, returns, inventory and COGS (HPP) management, accounts payable/receivable, and financial reporting.

---

## Table of Contents

- [Key Features](#key-features)
- [Business Flow](#business-flow)
- [Tech Stack](#tech-stack)
- [Architecture & Folder Structure](#architecture--folder-structure)
- [Security & Access Control](#security--access-control)
- [Installation](#installation)
- [Sample Endpoints](#sample-endpoints)
- [Testing](#testing)
- [Author](#author)

---

## Key Features

### Master Data
| Module | Description |
|---|---|
| Customer & Customer Group | Customer records and their grouping |
| Supplier | Supplier records |
| Product, Product Group, Unit | Product catalog, categories, and units of measure |
| Employee & Fleet | Employee and delivery vehicle records |
| Bank & Payment Method | Bank accounts and payment methods |
| Chart of Accounts | Account structure for financial records |
| Owner, Parameter, Holiday | General system configuration |

### Transactions
- **Orders** — customer order entry (header–detail).
- **Final Orders & Order Check** — consolidated final orders that drive purchasing and sales, including printable *delivery notes*.
- **Purchasing** — purchasing goods from suppliers, with product stock checks and daily shopping transactions.
- **Sales** — sales to customers, *invoice* printing, and profit reports (summary and detailed).
- **Purchase & Sales Invoices**.
- **Purchase Returns & Sales Returns** — with validation of the maximum returnable quantity.
- **Stock Adjustment** — physical stock corrections.
- **Fleet & Shopping Transactions** — vehicle operating costs and daily expenses.

### Finance
- **Accounts Payable & Payable Settlement** to suppliers.
- **Accounts Receivable & Receivable Settlement** from customers.

### Inventory
- **Stock Card** — per-product history of stock movements.
- **COGS (HPP)** — cost of goods sold calculated from purchases, sales, and returns.

### System & Administration
- Management of **Users, Roles, Menus, and ACL/ACOs** (permissions per role and per user).
- **Audit Trail** — records every data change (header & detail).
- **Dashboard**, **error log**, change password, and forgot password (email reset).
- **Running Number** — automatic document numbering by group/sub-group and period.
- **Excel export & reports** for nearly every module (PhpSpreadsheet).
- **Real-time notifications** for purchase and sales transactions via WebSockets.

---

## Business Flow

```
 Order ──► Final Order ──┬──► Purchase ──► Payable ──► Payable Settlement
                         │        │
                         │        └──► Purchase Return
                         │
                         └──► Sale ──► Receivable ──► Receivable Settlement
                                  │
                                  ├──► Invoice / Delivery Note
                                  └──► Sales Return

 Purchase / Sale / Returns / Stock Adjustment ──► Stock Card ──► COGS ──► Profit Report
```

---

## Tech Stack

| Category | Technology |
|---|---|
| Language | PHP 7.3+ / 8.x |
| Framework | Laravel 8 |
| Database | MySQL / MariaDB |
| Authentication | Laravel Passport (OAuth2), Laravel Sanctum |
| Real-time | `beyondcode/laravel-websockets`, Pusher protocol |
| Export / Reports | `phpoffice/phpspreadsheet` |
| Others | Intervention Image, Guzzle HTTP, Doctrine DBAL, Laravel CORS |
| Testing | PHPUnit, Paratest, Mockery, Faker |

---

## Architecture & Folder Structure

```
erp-api/
├── app/
│   ├── Console/              # Artisan kernel & scheduler
│   ├── Events/               # Broadcast events (purchase/sales notifications, export progress)
│   ├── Exceptions/           # Global exception handler
│   ├── Helpers/              # Global helper functions (autoloaded via Composer)
│   ├── Http/
│   │   ├── Controllers/Api/  # ~60 REST controllers, one per module
│   │   ├── Middleware/       # Authorized (ACL check), HandleTokenExpiration, etc.
│   │   └── Requests/         # Form Request validation (Store/Update/Destroy)
│   ├── Mail/                 # Password reset email
│   ├── Models/               # Eloquent models + grid query builders (filter, sort, paging)
│   ├── Providers/            # Service providers (Auth, Broadcast, Route, etc.)
│   ├── Rules/                # Custom validation rules
│   ├── Services/             # RunningNumberService (document numbering)
│   └── Traits/               # RestrictDeletion (prevents deleting referenced records)
├── config/                   # Laravel configuration + external API integration
├── cronjob/                  # Scheduled scripts (run via Task Scheduler / cron)
├── database/
│   ├── migrations/           # 60+ schema migrations
│   ├── factories/
│   └── seeders/              # Master data, menu, role & ACL seeders
├── routes/
│   ├── api.php               # All REST API endpoints
│   └── channels.php          # Broadcast channel authorization
├── tests/Feature/Api/        # API feature tests
└── public/                   # Application entry point
```

### Design Patterns
- **Header–Detail pattern** for transaction documents (e.g. `PenjualanHeader` + `PenjualanDetail`), so a single document can hold many line items.
- **Form Request Validation**, separated for create, update, and delete.
- **Standardized grid endpoints** in every module: server-side search, per-column filtering, sorting, and pagination.
- **Consistent supporting endpoints**: `/default` (form defaults), `/field_length` (field lengths for frontend validation), `/combo`, `/export`, `/report`.
- **Database transactions** when saving documents to keep data consistent.
- **Automatic audit trail** written to the `logtrail` table on every change.

---

## Security & Access Control

1. **OAuth2 authentication** with Laravel Passport — clients obtain a token via `POST /api/token`.
2. **`auth:api` middleware** protects all transactional endpoints.
3. **`Authorized` middleware** checks permissions based on the *class* (module) and *method* (action) against role and user ACL tables, returning `403` when access is denied.
4. **HandleTokenExpiration** handles expired tokens.
5. **RestrictDeletion** prevents deleting master data that is still referenced by transactions.

---

## Installation

### Requirements
- PHP 7.3+ with the `pdo_mysql`, `gd`, `zip`, and `mbstring` extensions
- Composer
- MySQL 5.7+ / MariaDB 10.3+

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/thinkabowtsya/erp-laravelapi.git
cd erp-laravelapi

# 2. Install dependencies
composer install

# 3. Copy the environment file and configure the database connection
cp .env.example .env
php artisan key:generate

# 4. Run migrations & seeders
php artisan migrate --seed

# 5. Generate Passport OAuth keys
php artisan passport:install

# 6. Start the server
php artisan serve
```

The API is available at `http://localhost:8000/api`.

### (Optional) WebSockets server for real-time notifications

Set `BROADCAST_DRIVER=pusher` and the `PUSHER_*` credentials in `.env`, then run:

```bash
php artisan websockets:serve
```

---

## Sample Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/token` | Log in & obtain an access token |
| `GET` | `/api/penjualanheader` | List sales (grid: filter, sort, paging) |
| `POST` | `/api/penjualanheader` | Create a sale (header + details) |
| `GET` | `/api/penjualanheader/{id}/invoice` | Invoice print data |
| `GET` | `/api/penjualanheader/reportprofit` | Sales profit report |
| `GET` | `/api/pembelianheader/cekstokproduct` | Check product stock before purchasing |
| `GET` | `/api/pesananfinalheader/{id}/suratpengantarbarang` | Delivery note data |
| `GET` | `/api/pelunasanpiutangheader/getpiutang` | Outstanding receivables |
| `GET` | `/api/customer/export` | Export customers to Excel |
| `GET` | `/api/logtrail/header` | Audit trail history |

All endpoints except `/api/token` require these headers:

```
Authorization: Bearer <access_token>
Accept: application/json
```

---

## Testing

```bash
php artisan test
```

Feature tests live in `tests/Feature/Api` (authentication, users, roles, parameters, errors).

---

## Author

**Syarifah** — Backend Developer

- GitHub: [@thinkabowtsya](https://github.com/thinkabowtsya)

---

> This repository is published as a portfolio project. Production data, credentials, and database dumps are not included.
