# Desa Digital API - Backend Technical Documentation

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Sanctum](https://img.shields.io/badge/Auth-Laravel%20Sanctum-red?style=for-the-badge)](https://laravel.com/docs/sanctum)
[![Spatie Permission](https://img.shields.io/badge/RBAC-Spatie%20Permission-blue?style=for-the-badge)](https://spatie.be/docs/laravel-permission)
[![Midtrans](https://img.shields.io/badge/Payment-Midtrans%20Snap-002D62?style=for-the-badge)](https://midtrans.com)
[![Course](https://img.shields.io/badge/Course-BuildWithAngga-orange?style=for-the-badge)](https://buildwithangga.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

> [!NOTE]
> ### Educational Project Disclaimer
> This project is developed as part of the **Full Stack Laravel and Vue.js** course curriculum by **[BuildWithAngga](https://buildwithangga.com)**. It serves as an enterprise-grade backend service powering digital village governance, resident administration, aid distribution, public infrastructure, and ticketing services.

---

## Table of Contents
1. [Architecture & Design Patterns](#1-architecture--design-patterns)
2. [Project Structure & Responsibilities](#2-project-structure--responsibilities)
3. [Business Logic & Domain Workflows](#3-business-logic--domain-workflows)
   - [3.1 Authentication & RBAC (Role-Based Access Control)](#31-authentication--rbac)
   - [3.2 Population & Demographics Management](#32-population--demographics-management)
   - [3.3 Social Assistance (Bansos) Lifecycle](#33-social-assistance-bansos-lifecycle)
   - [3.4 Village Events & Midtrans Snap Payment Gateway](#34-village-events--midtrans-snap-payment-gateway)
   - [3.5 Infrastructure Development Projects & Job Applicants](#35-infrastructure-development-projects--job-applicants)
   - [3.6 Village Profile & Media Gallery](#36-village-profile--media-gallery)
   - [3.7 Village Dashboard Statistics](#37-village-dashboard-statistics)
4. [Complete API Endpoints Reference](#4-complete-api-endpoints-reference)
5. [Getting Started & Installation Guide](#5-getting-started--installation-guide)

---

## 1. Architecture & Design Patterns

The backend follows a **Layered Clean Architecture** powered by the **Repository Pattern** and **Dependency Injection** to ensure strict decoupling between HTTP transport, business logic, and database operations.

```text
  [ Client Application (Vue.js / Next.js / Mobile) ]
                          │
                          ▼
             [ HTTP / Middleware Layer ]
   • Route Definitions (`routes/api.php`)
   • Authentication: Laravel Sanctum (Bearer Token)
   • Authorization: Spatie Permission (`role` & `permission` middleware)
                          │
                          ▼
            [ Request Validation Layer ]
   • Form Requests (`App\Http\Requests\*`)
                          │
                          ▼
               [ Controller Layer ]
   • Thin REST Controllers (`App\Http\Controllers\*`)
   • Injected Repository Interfaces via Service Container
                          │
                          ▼
         [ Repository Layer & Interfaces ]
   • Contracts: `App\Interfaces\*RepositoryInterface`
   • Implementations: `App\Repositories\*Repository`
   • Database Transactions (`DB::beginTransaction()`, `commit()`, `rollBack()`)
   • Third-Party SDK: Midtrans Snap API
                          │
                          ▼
               [ Data Access Layer ]
   • Eloquent Models (`App\Models\*`)
   • Trait UUID (Non-incrementing string UUID v4)
   • Trait SoftDeletes (Data audit retention without physical data loss)
                          │
                          ▼
            [ Response Transformer Layer ]
   • API Resources (`App\Http\Resources\*`)
   • Standardized Pagination Formatter (`PaginateResource`)
   • Standardized JSON Envelope Helper (`ResponseHelper`)
```

### Key Architectural Highlights
1. **Primary Key UUID v4 (`App\Traits\UUID`)**:
   All core tables employ string UUID v4 generated dynamically during Eloquent's `creating` event. This prevents sequential ID enumeration and crawler attacks.
2. **Soft Deletes**:
   Citizen and administrative records leverage `SoftDeletes` to maintain legal audit trails for village archives.
3. **Standardized Response Envelope (`ResponseHelper`)**:
   Uniform API contracts across all endpoints:
   ```json
   {
     "success": true,
     "message": "Data retrieved successfully",
     "data": { ... }
   }
   ```
4. **Decoupled Repositories**:
   Controllers depend solely on `Interfaces` bound in `RepositoryServiceProvider`. Any database driver, caching layer, or mock can be swapped without touching controllers.

---

## 2. Project Structure & Responsibilities

```text
desa-digital-api/
├── app/
│   ├── Helpers/
│   │   └── ResponseHelper.php           # Standard JSON response envelope
│   ├── Http/
│   │   ├── Controllers/                 # HTTP controllers (dispatching to repositories)
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DevelopmentApplicantController.php
│   │   │   ├── DevelopmentController.php
│   │   │   ├── EventController.php
│   │   │   ├── EventParticipantController.php
│   │   │   ├── FamilyMemberController.php
│   │   │   ├── HeadOfFamilyController.php
│   │   │   ├── MidtransController.php   # Webhook callback & SHA-512 verification
│   │   │   ├── ProfileController.php
│   │   │   ├── SocialAssistanceController.php
│   │   │   ├── SocialAssistanceRecipientController.php
│   │   │   └── UserController.php
│   │   ├── Requests/                    # FormRequest payload validation rules
│   │   └── Resources/                   # JSON transformers & presentation masking
│   │       ├── PaginateResource.php     # Reusable pagination meta transformer
│   │       └── ...
│   ├── Interfaces/                      # Repository contracts
│   │   ├── AuthRepositoryInterface.php
│   │   ├── DashboardRepositoryInterface.php
│   │   ├── DevelopmentApplicantRepositoryInterface.php
│   │   ├── DevelopmentRepositoryInterface.php
│   │   ├── EventParticipantRepositoryInterface.php
│   │   ├── EventRepositoryInterface.php
│   │   ├── FamilyMemberRepositoryInterface.php
│   │   ├── HeadOfFamilyRepositoryInterface.php
│   │   ├── ProfileRepositoryInterface.php
│   │   ├── SocialAssistanceRecipientRepositoryInterface.php
│   │   ├── SocialAssistanceRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   ├── Models/                          # Eloquent ORM Models & relationships
│   ├── Providers/
│   │   └── RepositoryServiceProvider.php# Interface-to-Implementation bindings
│   ├── Repositories/                    # Concrete data layer & transactional logic
│   └── Traits/
│       └── UUID.php                     # Auto UUID v4 assignment hook
├── bootstrap/
│   └── app.php                          # App bootstrap, middleware aliases & JSON exceptions
├── config/
│   ├── midtrans.php                     # Midtrans payment gateway credentials & settings
│   └── permission.php                   # Spatie Permission configuration
├── database/
│   ├── migrations/                      # UUID-backed database migrations
│   └── seeders/                         # Role, permission, and test accounts seeders
└── routes/
    ├── api.php                          # Protected & public REST route definitions
    └── console.php
```

---

## 3. Business Logic & Domain Workflows

### 3.1 Authentication & RBAC
- **Engine**: Laravel Sanctum (Personal Access Tokens) paired with Spatie Permission under the `sanctum` guard.
- **Roles**:
  - `admin`: Has root administrative access to all entities, program creations, approval workflows, and configuration.
  - `head-of-family`: Represents a verified resident (Family Head). Authorized to access public records, manage immediate family members, submit aid applications, register for events, and apply for village infrastructure jobs.
- **Login Flow**:
  1. Client sends `email` and `password` to `POST /api/login`.
  2. Authenticated via `Auth::guard('web')->attempt($credentials)`.
  3. A personal access token is issued via `$user->createToken('auth_token')->plainTextToken`.
  4. Subsequent requests pass `Authorization: Bearer <token>`.
- **Identity Endpoint (`/api/me`)**:
  Returns user profile, roles, assigned permissions, and linked `head_of_family` details for frontend state hydration.

---

### 3.2 Population & Demographics Management
- **Head of Family (`HeadOfFamily`)**:
  - Contains national ID card / KK number (`identity_number`), profile picture, phone number, occupation, and marital status.
  - Automatically provisions an associated `User` record assigned the `head-of-family` role.
- **Family Member (`FamilyMember`)**:
  - Attached to a specific `head_of_family_id`.
  - Automatically provisions a distinct `User` account for every individual member, enabling future self-service logins.
  - Includes kinship relation attributes (`relation`: e.g., `wife`, `child`, `parent`).
- **Data Isolation**:
  Citizen queries are scoped to their own household when requested by a `head-of-family` role:
  ```php
  if (auth()->user()->hasRole('head-of-family')) {
      $query->where('head_of_family_id', auth()->user()->headOfFamily->id);
  }
  ```

---

### 3.3 Social Assistance (Bansos) Lifecycle
1. **Creation**: Admin configures aid programs (`POST /api/social-assistance`) specifying budget amount, quota, and requirements.
2. **Citizen Application**: Head of Family applies via `POST /api/social-assistance-recipient`, uploading supporting proof (`proof`), bank details, account number, and reason (`reason`). Status defaults to `'pending'`.
3. **Verification & Disbursement**: Admin reviews uploaded proof and updates status to `'approved'` or `'rejected'` with the disbursed amount via `PUT /api/social-assistance-recipient/{id}`.

---

### 3.4 Village Events & Midtrans Snap Payment Gateway
Handles village festival tickets, market stall retributions, and public community event registrations.

```text
[ Resident / Frontend ]             [ Backend API ]               [ Midtrans Snap API ]
         │                               │                               │
         │── 1. POST /event-participant ─▶│                               │
         │   (event_id, qty, head_id)    │── 2. Compute total_price      │
         │                               │── 3. DB Transaction Commit    │
         │                               │      (status: 'pending')      │
         │                               │── 4. Request Snap Token ─────▶│
         │                               │◀── 5. Return Snap Token ──────│
         │◀── 6. JSON (snap_token) ──────│                               │
         │                                                               │
         │── 7. Open Midtrans Snap Popup in Browser ────────────────────▶│
         │   (Pay via QRIS, Virtual Account, GoPay, Card)                │
         │                                                               │
         │                      [ Payment Settled ]                      │
         │                               │                               │
         │                               │◀── 8. Webhook Callback POST ──│
         │                               │       (/api/midtrans-callback)│
         │                               │── 9. Verify SHA-512 Signature │
         │                               │── 10. Update payment_status   │
         │                               │       ('paid' / 'failed')     │
         │                               │── 11. Return 200 OK ─────────▶│
```

- **Price Computation**: `total_price = event.price * quantity`.
- **Snap Order Details**:
  - `order_id`: The `id` (UUID) of the `EventParticipant` record.
  - `gross_amount`: Exact total transaction amount.
- **Webhook Security Verification**:
  Validates incoming Midtrans notifications with SHA-512 signature hashing:
  ```php
  $hashedKey = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
  if ($hashedKey !== $request->signature_key) {
      return response()->json(['message' => 'Invalid signature key'], 403);
  }
  ```

---

### 3.5 Infrastructure Development Projects & Job Applicants
- **Development Projects (`Development`)**: Tracks village physical development (roads, irrigation, public halls) with project budgets, start/end dates, person in charge (`person_in_charge`), and lifecycle statuses (`planned`, `in_progress`, `completed`).
- **Applicant Tracking (`DevelopmentApplicant`)**: Village residents can apply for labor or vendor opportunities within village projects. Filter `status=my-applications` retrieves all projects applied for by the user's family members.

---

### 3.6 Village Profile & Media Gallery
- **Village Identity (`Profile`)**: Central record containing village name, village head (`headman`), total area, agricultural area, population count, and history.
- **Photo Showcase (`ProfileImage`)**: Supports multiple gallery uploads stored under `assets/profiles` to power public landing pages.

---

### 3.7 Village Dashboard Statistics
The `GET /api/dashboard/get-dashboard-data` endpoint provides fast aggregated metrics for admin dashboard cards:
- `residents`: Total population (Sum of Heads of Family + Family Members).
- `head_of_families`: Count of registered households.
- `social_assistances`: Count of active aid programs.
- `events`: Total public events.
- `developments`: Total village development initiatives.

---

## 4. Complete API Endpoints Reference

All secured endpoints require:
`Authorization: Bearer <token>` and `Accept: application/json`.

### Authentication
| Method | Endpoint | Description | Access | Payload / Notes |
|---|---|---|---|---|
| `POST` | `/api/login` | Authenticate user & issue Bearer token | Public | `email`, `password` |
| `POST` | `/api/register` | Register citizen user account | Public | `name`, `email`, `password` |
| `POST` | `/api/logout` | Revoke active access token | Auth | Bearer Token |
| `POST` | `/api/me` | Fetch active user, roles & permissions | Auth | Bearer Token |

### Dashboard & Village Profile
| Method | Endpoint | Description | Access |
|---|---|---|---|
| `GET` | `/api/dashboard/get-dashboard-data` | Aggregated statistical counts | Auth (`admin`, `head-of-family`) |
| `GET` | `/api/profile` | Retrieve village profile & photo gallery | Auth |
| `POST` | `/api/profile` | Initialize village profile & photos | Auth (`admin`) |
| `PUT` | `/api/profile` | Update village profile & upload photos | Auth (`admin`) |

### Demographics & Population
| Method | Endpoint | Description | Permission |
|---|---|---|---|
| `GET` | `/api/head-of-family` | List all Heads of Family | `head-of-family-list` |
| `GET` | `/api/head-of-family/all/paginated` | Paginated Heads of Family (`?search=&row_per_page=`) | `head-of-family-list` |
| `POST` | `/api/head-of-family` | Create Head of Family (+ linked User account) | `head-of-family-create` |
| `GET` | `/api/head-of-family/{id}` | Detailed Head of Family with all family members | `head-of-family-list` |
| `PUT` | `/api/head-of-family/{id}` | Update Head of Family & associated User | `head-of-family-edit` |
| `DELETE` | `/api/head-of-family/{id}` | Soft delete Head of Family | `head-of-family-delete` |
| `GET` | `/api/family-member` | List family members (scoped by family head) | `family-member-list` |
| `GET` | `/api/family-member/all/paginated` | Paginated family members list | `family-member-list` |
| `POST` | `/api/family-member` | Create family member (+ linked User account) | `family-member-create` |
| `GET` | `/api/family-member/{id}` | Detailed family member | `family-member-list` |
| `PUT` | `/api/family-member/{id}` | Update family member & associated User | `family-member-edit` |
| `DELETE` | `/api/family-member/{id}` | Soft delete family member | `family-member-delete` |

### Social Assistance (Bansos)
| Method | Endpoint | Description | Permission |
|---|---|---|---|
| `GET` | `/api/social-assistance` | List social aid programs | `social-assistance-list` |
| `GET` | `/api/social-assistance/all/paginated` | Paginated aid programs | `social-assistance-list` |
| `POST` | `/api/social-assistance` | Create social assistance program | `social-assistance-create` |
| `GET` | `/api/social-assistance/{id}` | Detailed aid program | `social-assistance-list` |
| `PUT` | `/api/social-assistance/{id}` | Update aid program | `social-assistance-edit` |
| `DELETE` | `/api/social-assistance/{id}` | Remove aid program | `social-assistance-delete` |
| `GET` | `/api/social-assistance-recipient` | List recipients (scoped for citizen) | `social-assistance-recipient-list` |
| `GET` | `/api/social-assistance-recipient/all/paginated` | Paginated recipients list | `social-assistance-recipient-list` |
| `POST` | `/api/social-assistance-recipient` | Submit aid application (proof upload) | `social-assistance-recipient-create` |
| `GET` | `/api/social-assistance-recipient/{id}` | View recipient application details | `social-assistance-recipient-list` |
| `PUT` | `/api/social-assistance-recipient/{id}` | Approve/reject application & set amount | `social-assistance-recipient-edit` |
| `DELETE` | `/api/social-assistance-recipient/{id}` | Remove recipient application | `social-assistance-recipient-delete` |

### Village Events & Midtrans Ticketing
| Method | Endpoint | Description | Permission |
|---|---|---|---|
| `GET` | `/api/event` | List all community events | `event-list` |
| `GET` | `/api/event/all/paginated` | Paginated community events | `event-list` |
| `POST` | `/api/event` | Create new event (poster, price, quota) | `event-create` |
| `GET` | `/api/event/{id}` | View event details | `event-list` |
| `PUT` | `/api/event/{id}` | Update event details | `event-edit` |
| `DELETE` | `/api/event/{id}` | Delete event | `event-delete` |
| `GET` | `/api/event-participant` | List event registrations | `event-participant-list` |
| `GET` | `/api/event-participant/all/paginated` | Paginated participant list | `event-participant-list` |
| `POST` | `/api/event-participant` | Register & generate Midtrans Snap Token | `event-participant-create` |
| `GET` | `/api/event-participant/{id}` | Retrieve registration ticket & snap token | `event-participant-list` |
| `PUT` | `/api/event-participant/{id}` | Update participant registration | `event-participant-edit` |
| `DELETE` | `/api/event-participant/{id}` | Cancel participant registration | `event-participant-delete` |
| `POST` | `/api/midtrans-callback` | Webhook notification receiver from Midtrans | Public (Signature Verified) |

### Infrastructure Development Projects
| Method | Endpoint | Description | Permission |
|---|---|---|---|
| `GET` | `/api/development` | List infrastructure projects | `development-list` |
| `GET` | `/api/development/all/paginated` | Paginated projects (`?status=my-applications`) | `development-list` |
| `POST` | `/api/development` | Create infrastructure project | `development-create` |
| `GET` | `/api/development/{id}` | Detailed project view | `development-list` |
| `PUT` | `/api/development/{id}` | Update project info & status | `development-edit` |
| `DELETE` | `/api/development/{id}` | Delete project | `development-delete` |
| `GET` | `/api/development-applicant` | List project applicants | `development-applicant-list` |
| `GET` | `/api/development-applicant/all/paginated` | Paginated applicants list | `development-applicant-list` |
| `POST` | `/api/development-applicant` | Apply for village infrastructure job | `development-applicant-create` |
| `GET` | `/api/development-applicant/{id}` | View applicant submission | `development-applicant-list` |
| `PUT` | `/api/development-applicant/{id}` | Update application status | `development-applicant-edit` |
| `DELETE` | `/api/development-applicant/{id}` | Delete job application | `development-applicant-delete` |

---

## 5. Getting Started & Installation Guide

### Prerequisites
- **PHP >= 8.3** (Required extensions: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `sqlite3` or `pdo_mysql`)
- **Composer >= 2.x**
- Free Sandbox Account on [Midtrans](https://dashboard.sandbox.midtrans.com)

### Installation Steps

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/galihhsw/desa-digital-api.git
   cd desa-digital-api
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment Variables**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Setup Database & Midtrans Keys**:
   Edit `.env`:
   ```env
   # Database (SQLite default)
   DB_CONNECTION=sqlite

   # Or MySQL:
   # DB_CONNECTION=mysql
   # DB_HOST=127.0.0.1
   # DB_PORT=3306
   # DB_DATABASE=desa_digital
   # DB_USERNAME=root
   # DB_PASSWORD=

   # Midtrans Gateway Configuration
   MIDTRANS_SERVER_KEY=SB-Mid-server-YourSandboxServerKey
   MIDTRANS_CLIENT_KEY=SB-Mid-client-YourSandboxClientKey
   MIDTRANS_IS_PRODUCTION=false
   MIDTRANS_IS_SANITIZED=true
   MIDTRANS_IS_3DS=true
   ```

5. **Create Storage Symlink**:
   ```bash
   php artisan storage:link
   ```

6. **Run Migrations & Seed Default Data**:
   ```bash
   php artisan migrate:fresh --seed
   ```

   **Default Demo Accounts:**
   | Role | Email | Password | Privileges |
   |---|---|---|---|
   | **Village Admin** | `admin@gunan.id` | `admin123` | Full administrative root access |
   | **Head of Family** | `headOfFamily@gunan.id` | `kepala123` | Resident & household self-service |

7. **Start Development Server**:
   ```bash
   php artisan serve
   ```
   API Base URL: `http://127.0.0.1:8000/api`

---

## License
This project is open-sourced under the [MIT License](LICENSE).
