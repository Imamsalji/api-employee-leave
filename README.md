# 📋 Leave Management API

> RESTful API Aplikasi Manajemen Cuti Karyawan — dibangun dengan Laravel 12, clean architecture, dan fully tested.

---

## 📑 Daftar Isi

- [Gambaran Umum](#gambaran-umum)
- [Tech Stack](#tech-stack)
- [Instalasi & Setup](#instalasi--setup)
- [Konfigurasi .env](#konfigurasi-env)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Autentikasi](#autentikasi)
- [Arsitektur Sistem](#arsitektur-sistem)
- [Struktur Database](#struktur-database)
- [Struktur Direktori](#struktur-direktori)
- [API Endpoints](#api-endpoints)
- [Business Rules](#business-rules)
- [Alur Sistem](#alur-sistem)
- [Response Format](#response-format)

---

## Gambaran Umum

Leave Management API adalah sistem manajemen cuti karyawan berbasis RESTful API yang memungkinkan:

- **Employee** — mengajukan cuti, melihat riwayat pengajuan, dan memantau sisa kuota.
- **Admin** — melihat seluruh pengajuan dari semua karyawan, menyetujui (approve), atau menolak (reject) pengajuan.

Sistem ini dibangun menggunakan prinsip **Clean Architecture** dengan pemisahan tanggung jawab yang tegas antar layer, sehingga mudah di-maintain, di-test, dan di-scale.

---

## Tech Stack

| Komponen             | Teknologi                               |
| -------------------- | --------------------------------------- |
| Framework            | Laravel 12                              |
| Authentication       | Laravel Sanctum (Token-based)           |
| OAuth / Social Login | Laravel Socialite                       |
| Database             | MySQL 8+ / PostgreSQL 14+               |
| File Storage         | Laravel Storage (local / S3-compatible) |
| Testing              | PHPUnit 11                              |
| PHP                  | 8.2+                                    |

---

## Instalasi & Setup

### Prasyarat

Pastikan environment kamu memiliki:

- PHP >= 8.2 dengan ekstensi: `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- Composer >= 2.x
- MySQL >= 8.0 atau PostgreSQL >= 14
- Node.js >= 18 (opsional, hanya jika ada frontend)

### Langkah Instalasi

**1. Clone repository**

```bash
git clone https://github.com/username/leave-management-api.git
cd leave-management-api
```

**2. Install dependencies PHP**

```bash
composer install
```

**3. Salin file environment**

```bash
cp .env.example .env
```

**4. Generate application key**

```bash
php artisan key:generate
```

**5. Konfigurasi `.env`** (lihat bagian [Konfigurasi .env](#konfigurasi-env))

**6. Buat database**

```sql
CREATE DATABASE leave_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**7. Jalankan migration**

```bash
php artisan migrate
```

**8. (Opsional) Jalankan seeder**

```bash
# Seed data user dummy dan kuota cuti
php artisan db:seed

# Atau seed spesifik
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=LeaveQuotaSeeder
```

**9. Buat symbolic link untuk storage**

```bash
php artisan storage:link
```

**10. Jalankan server**

```bash
php artisan serve
```

API akan berjalan di `http://localhost:8000`.

---

## Konfigurasi .env

Berikut penjelasan lengkap setiap variabel yang perlu dikonfigurasi:

### Aplikasi

```dotenv
APP_NAME="Leave Management API"
APP_ENV=local          # local | staging | production
APP_KEY=               # Di-generate otomatis via: php artisan key:generate
APP_DEBUG=true         # Set false di production
APP_URL=http://localhost:8000
```

> ⚠️ **Production**: Wajib set `APP_DEBUG=false` dan `APP_ENV=production`.

### Database

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

**Untuk PostgreSQL**, ubah:

```dotenv
DB_CONNECTION=pgsql
DB_PORT=5432
```

### Authentication (Sanctum)

```dotenv
# Domain yang diizinkan mengakses API (pisahkan dengan koma)
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1,localhost:3000

# Durasi token (dalam menit). Null = tidak expired
SESSION_LIFETIME=120
```

### OAuth — Laravel Socialite

Konfigurasi provider OAuth yang digunakan. Daftarkan aplikasi di masing-masing developer console untuk mendapatkan `CLIENT_ID` dan `CLIENT_SECRET`.

```dotenv
# Google OAuth
# Daftarkan di: https://console.cloud.google.com
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URL=http://localhost:8000/api/auth/google/callback

```

> Pastikan `REDIRECT_URL` yang didaftarkan di Google Console **sama persis** dengan nilai di `.env`.

### File Storage

```dotenv
# Untuk development (local)
FILESYSTEM_DISK=public
```

### Cache & Queue

```dotenv
CACHE_STORE=file        # file | redis | database
QUEUE_CONNECTION=sync   # sync (development) | redis | database (production)

# Jika menggunakan Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Contoh `.env` Lengkap untuk Development

```dotenv
APP_NAME="Leave Management API"
APP_ENV=local
APP_KEY=base64:your_generated_key_here
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=leave_management
DB_USERNAME=root
DB_PASSWORD=secret

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

---

## Menjalankan Aplikasi

```bash
# Development server
php artisan serve

# Jalankan queue worker (jika QUEUE_CONNECTION != sync)
php artisan queue:work

# Clear semua cache
php artisan optimize:clear

# Cache untuk production
php artisan optimize
```

---

## Autentikasi

Sistem mendukung dua metode login yang bisa digunakan bersamaan.

### 1. Login Konvensional

Login menggunakan email dan password. Setelah berhasil, server mengembalikan Sanctum token yang digunakan sebagai Bearer Token di setiap request selanjutnya.

| Method | Endpoint             | Deskripsi                     |
| ------ | -------------------- | ----------------------------- |
| `POST` | `/api/auth/register` | Registrasi akun baru          |
| `POST` | `/api/auth/login`    | Login dengan email & password |
| `POST` | `/api/auth/logout`   | Logout (hapus token aktif)    |

### 2. OAuth via Laravel Socialite

Login menggunakan akun Google (atau provider lain yang dikonfigurasi). Alurnya:

1. Client meminta URL redirect ke provider → `GET /api/auth/{provider}/redirect`
2. User login dan izinkan akses di halaman provider (Google, dll.)
3. Provider redirect kembali ke callback URL dengan authorization code
4. Server menukar code dengan data user, lalu mengembalikan Sanctum token

| Method | Endpoint                        | Deskripsi                                |
| ------ | ------------------------------- | ---------------------------------------- |
| `GET`  | `/api/auth/{provider}/redirect` | Dapatkan URL redirect ke provider OAuth  |
| `GET`  | `/api/auth/{provider}/callback` | Callback dari provider, kembalikan token |

`{provider}` yang didukung: `google`, `github` (sesuai konfigurasi `.env`).

**Contoh alur OAuth:**

```
Client                    Server                    Google
  │                          │                         │
  ├─ GET /auth/google/redirect ─►                      │
  │  ◄─ { redirect_url } ────┤                         │
  │                          │                         │
  ├─ Buka redirect_url di browser ───────────────────► │
  │                          │      User login & izin  │
  │  ◄────────────────────────────── redirect callback ┤
  │                          │                         │
  ├─ GET /auth/google/callback ──►                     │
  │                          ├─ tukar code → user data  │
  │  ◄─ { token, user } ─────┤                         │
```

**Response setelah login OAuth berhasil:**

```json
{
    "success": true,
    "message": "Login berhasil.",
    "data": {
        "token": "1|abc123xyz...",
        "token_type": "Bearer",
        "user": {
            "id": 5,
            "name": "Budi Santoso",
            "email": "budi@gmail.com",
            "role": "employee"
        }
    }
}
```

> Token yang dikembalikan adalah Sanctum token — gunakan sebagai `Authorization: Bearer {token}` di semua request API selanjutnya, sama seperti login konvensional.

---

## Arsitektur Sistem

Aplikasi menggunakan **Layered Clean Architecture** dengan 4 layer utama. Setiap layer hanya boleh bergantung ke layer di bawahnya, tidak pernah ke atas.

```
HTTP Layer → Business Layer → Data Layer → Infrastructure Layer
```

### Layer & Komponen

**HTTP Layer** — menangani request masuk dan response keluar

- `Middleware` — memvalidasi role user sebelum masuk ke Controller
- `Form Request` — memvalidasi input dan otorisasi akses
- `Controller` — hanya menerima request dan memanggil Service (thin controller)
- `API Resource` — mengontrol field apa saja yang keluar ke response JSON
- `ApiResponse Trait` — memastikan format JSON konsisten di seluruh endpoint

**Business Layer** — tempat semua aturan bisnis hidup

- `LeaveService` — satu-satunya tempat business logic: cek kuota, cek overlap, hitung hari, dan simpan data secara atomik via `DB::transaction()`
- `LeaveException` — custom exception dengan named constructor agar error lebih deskriptif

**Data Layer** — menangani akses dan persistensi data

- `Repository Interface` — kontrak akses data, memisahkan Service dari implementasi Eloquent
- `Repository Implementation` — implementasi query Eloquent yang konkret
- `Eloquent Model` — mendefinisikan struktur, relasi, scope, dan constants status

**Infrastructure Layer** — fondasi teknis aplikasi

- `Migration` — version control skema database
- `RepositoryServiceProvider` — mendaftarkan binding Interface → Implementation ke IoC Container
- `Storage` — abstraksi penyimpanan file, bisa swap lokal ke S3 hanya via `.env`

---

## Struktur Database

### Diagram Relasi

```
users
 ├── id (PK)
 ├── name
 ├── email
 ├── role          ENUM('admin', 'employee')
 ├── jabatan
 ├── divisi
 ├── password
 └── timestamps

leave_quotas                          leave_requests
 ├── id (PK)                           ├── id (PK)
 ├── user_id (FK → users)              ├── user_id (FK → users)
 ├── year                              ├── start_date
 ├── total_days   DEFAULT 12           ├── end_date
 ├── used_days    DEFAULT 0            ├── total_days
 ├── remaining_days DEFAULT 12         ├── reason
 └── timestamps                        ├── status   ENUM('pending','approved','rejected')
                                       ├── rejection_reason
                                       ├── approved_by (FK → users)
                                       ├── approved_at
                                       └── timestamps

leave_attachments
 ├── id (PK)
 ├── leave_request_id (FK → leave_requests)
 ├── file_name
 ├── file_path
 ├── file_type
 ├── file_size
 └── timestamps
```

---

## Struktur Direktori

```
app/
├── Exceptions/
│   └── LeaveException.php          # Custom exception dengan named constructors
│
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── Employee/
│   │       │   └── LeaveRequestController.php   # Endpoint employee
│   │       └── Admin/
│   │           └── LeaveApprovalController.php  # Endpoint admin
│   │
│   ├── Middleware/
│   │   └── RoleMiddleware.php       # Guard akses berdasarkan role
│   │
│   ├── Requests/
│   │   ├── SubmitLeaveRequest.php   # Validasi pengajuan cuti
│   │   └── ReviewLeaveRequest.php  # Validasi approve/reject
│   │
│   └── Resources/
│       ├── LeaveRequestResource.php     # Transformasi single object
│       ├── LeaveRequestCollection.php   # Transformasi paginated list
│       ├── LeaveAttachmentResource.php  # Transformasi attachment
│       └── LeaveQuotaResource.php       # Transformasi kuota
│
├── Models/
│   ├── User.php                # Role constants, relasi, helper isAdmin/isEmployee
│   ├── LeaveRequest.php        # Status constants, scopes, status helpers
│   ├── LeaveQuota.php          # deductDays(), restoreDays(), scopes
│   └── LeaveAttachment.php     # Accessor url & file_size_formatted
│
├── Providers/
│   └── RepositoryServiceProvider.php  # Binding Interface → Implementation
│
├── Repositories/
│   ├── Contracts/
│   │   ├── LeaveRequestRepositoryInterface.php
│   │   └── LeaveQuotaRepositoryInterface.php
│   ├── LeaveRequestRepository.php
│   └── LeaveQuotaRepository.php
│
├── Services/
│   └── LeaveService.php        # Seluruh business logic cuti
│
└── Traits/
    └── ApiResponse.php         # Format response JSON yang konsisten

database/
├── factories/
│   ├── UserFactory.php
│   ├── LeaveRequestFactory.php
│   └── LeaveQuotaFactory.php
│
└── migrations/
    ├── ..._add_jabatan_divisi_to_users_table.php
    ├── ..._create_leave_quotas_table.php
    ├── ..._create_leave_requests_table.php
    └── ..._create_leave_attachments_table.php

tests/
├── Feature/
│   └── Leave/
│       ├── LeaveTestCase.php        # Base test dengan helper methods
│       ├── SubmitLeaveTest.php      # 19 test cases
│       └── ApproveLeaveTest.php     # 14 test cases
│
└── Unit/
    └── Services/
        └── LeaveServiceTest.php     # 7 unit test cases
```

---

## API Endpoints

### Authentication Endpoints

| Method | Endpoint             | Deskripsi                          |
| ------ | -------------------- | ---------------------------------- |
| `POST` | `/api/auth/register` | Registrasi akun baru               |
| `POST` | `/api/auth/login`    | Login dengan email & password      |
| `POST` | `/api/auth/logout`   | Logout (butuh token)               |
| `GET`  | `/api/auth/google`   | Redirect ke halaman OAuth provider |
| `GET`  | `/api/auth/google`   | Callback OAuth, kembalikan token   |

### Authentication

Semua endpoint memerlukan header:

```
Authorization: Bearer {sanctum_token}
Content-Type: application/json
Accept: application/json
```

### Employee Endpoints

| Method | Endpoint                     | Deskripsi                             |
| ------ | ---------------------------- | ------------------------------------- |
| `GET`  | `/api/employee/leaves`       | Daftar cuti milik employee yang login |
| `POST` | `/api/employee/leaves`       | Ajukan cuti baru                      |
| `GET`  | `/api/employee/leaves/{id}`  | Detail satu pengajuan cuti            |
| `GET`  | `/api/employee/leaves/quota` | Cek sisa kuota cuti tahun ini         |

### Admin Endpoints

| Method | Endpoint                        | Deskripsi                               |
| ------ | ------------------------------- | --------------------------------------- |
| `GET`  | `/api/admin/leaves`             | Semua pengajuan cuti (seluruh karyawan) |
| `GET`  | `/api/admin/leaves/{id}`        | Detail satu pengajuan cuti              |
| `PUT`  | `/api/admin/leaves/{id}/review` | Approve atau reject pengajuan           |

### Query Parameters

**GET `/api/employee/leaves`** dan **GET `/api/admin/leaves`**:

| Parameter  | Tipe    | Contoh    | Deskripsi                            |
| ---------- | ------- | --------- | ------------------------------------ |
| `status`   | string  | `pending` | Filter berdasarkan status            |
| `year`     | integer | `2025`    | Filter berdasarkan tahun             |
| `user_id`  | integer | `5`       | Filter berdasarkan user (admin only) |
| `page`     | integer | `2`       | Halaman pagination                   |
| `per_page` | integer | `10`      | Jumlah item per halaman              |

### Request Body

**POST `/api/employee/leaves`**:

```json
{
    "start_date": "2025-06-02",
    "end_date": "2025-06-06",
    "reason": "Keperluan keluarga yang tidak bisa ditunda.",
    "attachments": ["(file upload: pdf/jpg/png, max 2MB, max 3 files)"]
}
```

**PUT `/api/admin/leaves/{id}/review`**:

```json
{
    "action": "rejected",
    "rejection_reason": "Sedang ada deadline project bulan ini."
}
```

> `rejection_reason` wajib diisi jika `action` adalah `rejected`.

---

## Business Rules

### Kuota Cuti

- Setiap employee mendapat **12 hari cuti per tahun**.
- Kuota dihitung per tahun kalender (1 Januari — 31 Desember).
- Sistem otomatis membuat record kuota saat employee pertama kali mengajukan cuti di tahun tersebut.
- Kuota dipotong hanya saat cuti **di-approve**, bukan saat diajukan.
- Jika cuti di-reject, kuota tidak berkurang.

### Pengajuan Cuti

- `start_date` tidak boleh di masa lalu (minimal hari ini).
- `end_date` harus sama atau setelah `start_date`.
- Total hari dihitung secara **inclusive** — cuti 1 Jan s/d 3 Jan = 3 hari.
- Tidak boleh ada overlap dengan cuti lain yang berstatus `pending` atau `approved`.
- Sisa kuota harus mencukupi jumlah hari yang diajukan.

### Workflow Status

```
                    ┌─────────┐
                    │ PENDING │  ← status default saat diajukan
                    └────┬────┘
                         │
              ┌──────────┴──────────┐
              ▼                     ▼
        ┌──────────┐          ┌──────────┐
        │ APPROVED │          │ REJECTED │
        └──────────┘          └──────────┘
        (kuota dipotong)    (kuota tidak berubah)
```

- Status `approved` atau `rejected` **tidak dapat diubah kembali** — permanen.
- Hanya admin yang bisa melakukan review (approve/reject).
- Hanya employee yang bisa mengajukan cuti.

### File Attachment

- Format yang diterima: `pdf`, `jpg`, `jpeg`, `png`.
- Ukuran maksimal per file: **2 MB**.
- Jumlah maksimal file per pengajuan: **3 file**.
- File disimpan di `storage/app/public/leave-attachments/{leave_id}/`.

---

## Alur Sistem

### Pengajuan Cuti (Employee)

1. Employee kirim `POST /api/employee/leaves`
2. Middleware memvalidasi token dan role
3. `SubmitLeaveRequest` memvalidasi input (tanggal, reason, file)
4. `LeaveService` menjalankan validasi bisnis secara berurutan:
    - Pastikan `end_date >= start_date`
    - Hitung total hari (inclusive)
    - Cek tidak ada overlap dengan cuti lain yang pending/approved
    - Cek sisa kuota mencukupi
5. Jika lolos semua validasi, simpan data dalam `DB::transaction()` (atomik)
6. Response `201` dengan data cuti yang baru dibuat

### Persetujuan Cuti (Admin)

1. Admin kirim `PUT /api/admin/leaves/{id}/review` dengan `action: approved/rejected`
2. Middleware memvalidasi token dan role admin
3. `ReviewLeaveRequest` memvalidasi action dan rejection_reason
4. `LeaveService` memproses:
    - Cek cuti exist dan masih berstatus `pending`
    - Jika **approve** → potong kuota dalam `DB::transaction()`
    - Jika **reject** → ubah status saja, kuota tidak berubah
5. Response `200` dengan data cuti yang sudah diupdate

---

## Response Format

Seluruh response menggunakan format JSON yang konsisten:

### Sukses

```json
{
    "success": true,
    "message": "Pengajuan cuti berhasil dikirim.",
    "data": {}
}
```

### Sukses dengan Pagination

```json
{
    "success": true,
    "message": "Daftar cuti berhasil diambil.",
    "data": [],
    "meta": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 15,
        "total": 42
    },
    "links": {
        "first": "http://localhost:8000/api/employee/leaves?page=1",
        "last": "http://localhost:8000/api/employee/leaves?page=3",
        "prev": null,
        "next": "http://localhost:8000/api/employee/leaves?page=2"
    }
}
```

### Error Validasi (422)

```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "start_date": ["Tanggal mulai tidak boleh kurang dari hari ini."],
        "end_date": ["Tanggal selesai harus sama atau setelah tanggal mulai."]
    }
}
```

### Error Business Logic (422)

```json
{
    "success": false,
    "message": "Kuota cuti tidak mencukupi. Sisa kuota: 2 hari, diajukan: 5 hari.",
    "errors": []
}
```

### Error Tidak Ditemukan (404)

```json
{
    "success": false,
    "message": "Pengajuan cuti tidak ditemukan.",
    "errors": []
}
```

### Error Tidak Diizinkan (403)

```json
{
    "success": false,
    "message": "Anda tidak memiliki akses ke halaman ini.",
    "errors": []
}
```

## Lisensi

Proyek ini menggunakan lisensi [MIT](LICENSE).
