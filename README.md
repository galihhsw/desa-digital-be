# Desa Digital API 🌾

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Sanctum](https://img.shields.io/badge/Auth-Laravel%20Sanctum-red?style=for-the-badge)](https://laravel.com/docs/sanctum)
[![License](https://img.shields.io/badge/License-MIT-blue?style=for-the-badge)](LICENSE)

Robust RESTful Backend API powering the **Desa Digital (Digital Village)** Information & Governance System. Provides centralized services for citizen records, social assistance programs, village events, and infrastructure development applications.

---

## 🌟 Core Features

- **Authentication & RBAC**: Token-based authentication using **Laravel Sanctum** paired with **Spatie Laravel-Permission** (`admin`, `head-of-family`).
- **Demographics & Population**: End-to-end management for Family Heads (KK) and Family Members.
- **Social Assistance (Bansos)**: Lifecycle management of aid programs, eligibility criteria, and recipient tracking.
- **Village Events**: Event creation, announcement broadcasts, and resident attendance/participation logging.
- **Infrastructure Development**: Proposal intake, project status workflows, and applicant tracking.
- **User & Profile Management**: Account management, credential updates, and village identity profiles.

---

## 🛠️ Tech Stack

- **Framework**: [Laravel 12](https://laravel.com)
- **Language**: PHP 8.3+
- **Authentication**: Laravel Sanctum
- **Authorization / RBAC**: Spatie Laravel-Permission
- **Database**: SQLite (default/dev), MySQL / PostgreSQL (production-ready)
- **Architecture**: RESTful JSON API with Form Request validation & API Resources

---

## 📁 Project Structure

```text
desa-digital-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # REST API Controllers (User, Family, Social, Event, etc.)
│   │   ├── Requests/        # Form Request validation classes
│   │   └── Resources/       # API Resource / JSON response transformers
│   └── Models/              # Eloquent Models & relationships
├── database/
│   ├── factories/           # Model factories for testing data
│   ├── migrations/          # Database schemas
│   └── seeders/             # Role, permission, and demo seeders
├── routes/
│   └── api.php              # RESTful API route definitions
└── tests/                   # Automated feature & unit test suite
```

---

## 🚀 Getting Started

### Prerequisites

Ensure the following tools are installed on your machine:
- **PHP >= 8.3** (with `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `sqlite3` extensions)
- **Composer**
- **Git**

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/galihhsw/desa-digital-be.git
   cd desa-digital-be
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Configure Environment Variables**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Note: Default database configuration is set to SQLite. Adjust `.env` if using MySQL or PostgreSQL.*

4. **Run Migrations & Seeders**
   ```bash
   php artisan migrate --seed
   ```

   > **Default Seed Accounts:**
   > - **Administrator**: `admin@gunan.id` | Password: `admin123`
   > - **Head of Family**: `headOfFamily@gunan.id` | Password: `kepala123`

5. **Start Local Development Server**
   ```bash
   php artisan serve
   ```
   Base API URL: `http://127.0.0.1:8000`

---

## 📑 API Endpoints Reference

All protected endpoints require a Bearer token via `Authorization: Bearer <token>`.

### Authentication
| Method | Endpoint | Description | Access |
|---|---|---|---|
| `POST` | `/api/login` | Authenticate user & issue Sanctum token | Public |
| `POST` | `/api/register` | Register new user account | Public |
| `POST` | `/api/logout` | Revoke current user token | Authenticated |
| `POST` | `/api/me` | Fetch authenticated user profile | Authenticated |

### Core Resources
| Method | Endpoint | Description |
|---|---|---|
| `API Resource` | `/api/user` | Manage users (CRUD) |
| `GET` | `/api/user/all/paginated` | Paginated users list |
| `API Resource` | `/api/head-of-family` | Manage Family Heads (CRUD) |
| `GET` | `/api/head-of-family/all/paginated` | Paginated Family Heads list |
| `API Resource` | `/api/family-member` | Manage Family Members (CRUD) |
| `GET` | `/api/family-member/all/paginated` | Paginated Family Members list |
| `API Resource` | `/api/social-assistance` | Manage Social Assistance programs (CRUD) |
| `GET` | `/api/social-assistance/all/paginated` | Paginated Social Assistance programs |
| `API Resource` | `/api/social-assistance-recipient` | Manage Assistance Recipients (CRUD) |
| `GET` | `/api/social-assistance-recipient/all/paginated` | Paginated Recipients list |
| `API Resource` | `/api/event` | Manage Village Events (CRUD) |
| `GET` | `/api/event/all/paginated` | Paginated Events list |
| `API Resource` | `/api/event-participant` | Manage Event Participants (CRUD) |
| `GET` | `/api/event-participant/all/paginated` | Paginated Participants list |
| `API Resource` | `/api/development` | Manage Development Projects (CRUD) |
| `GET` | `/api/development/all/paginated` | Paginated Projects list |
| `API Resource` | `/api/development-applicant` | Manage Development Applicants (CRUD) |
| `GET` | `/api/development-applicant/all/paginated` | Paginated Applicants list |
| `GET/POST/PUT` | `/api/profile` | View or update village profile |

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).
