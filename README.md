# UTS Task Tracker — `uts-pl-24-543374-sv-25201`

Proyek Ujian Tengah Semester (UTS) mata kuliah **Evolusi dan Konstruksi Perangkat Lunak**.  
Aplikasi **Task Tracker** berbasis REST API dan Single Page Application (SPA) yang dirancang dengan arsitektur monorepo, containerization, dan pipeline CI/CD otomatis.

---

## Identitas Pengembang

- **Nama:** Raihan Esfandyka Suwandi
- **NIM:** 24/543374/SV/25201
- **Nama Repository:** `uts-pl-24-543374-sv-25201`

---

## Arsitektur & Teknologi

| Komponen | Teknologi / Image | Keterangan |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel 11 | REST API backend berbasis PHP 8.3 |
| **Autentikasi API** | Laravel Sanctum | Token-based authentication |
| **Application Server** | FrankenPHP (`dunglas/frankenphp:1.4-php8.3`) | Server PHP modern berbasis Caddy |
| **Database** | PostgreSQL 16 (`postgres:16-alpine`) | Relational database container |
| **Frontend Framework** | Vue 3 + Vite | Single Page Application (SPA) modern |
| **Styling** | Tailwind CSS | Utility-first CSS styling |
| **HTTP Client** | Axios | REST API communication |
| **Frontend Container** | Multi-stage Build (`node:20-alpine` ➔ `nginx:1.27-alpine`) | Production static build & reverse proxy |
| **Orkestrasi** | Docker Compose | Manajemen multi-container lokal |
| **Continuous Integration (CI)** | GitHub Actions | Automated backend tests & frontend build |
| **Continuous Delivery (CD)** | GitHub Actions ➔ GHCR (`ghcr.io`) | Build & push container images dengan tag Commit SHA |
| **Dokumentasi API** | Postman Collection v2.1 | Dokumentasi & pengujian endpoint API |
| **Laporan** | Dokumen PDF | Laporan teknis UTS |

---

## Struktur Monorepo

```text
uts-pl-24-543374-sv-25201/
├── backend/            # Aplikasi Laravel 11 (REST API, Sanctum, PHP 8.3)
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── routes/
│   ├── composer.json
│   └── artisan
├── frontend/           # Aplikasi Vue 3 + Vite (SPA, Axios, Tailwind CSS)
│   ├── src/
│   ├── index.html
│   ├── package.json
│   └── vite.config.js
├── .github/            # GitHub Actions CI/CD workflows (disiapkan pada tahap terkait)
├── .gitignore          # Git ignore terpadu untuk backend, frontend, dan docker
└── README.md           # Dokumentasi proyek
```

---

## Skema Data Task (Spesifikasi Inti)

- `id`: Primary Key (BigInteger / UUID)
- `title`: String (Judul tugas)
- `description`: Text (Deskripsi tugas)
- `status`: Enum / String (`pending`, `completed`)
- `due_date`: Date / Timestamp (Batas waktu tugas)
- `created_at`: Timestamp
- `updated_at`: Timestamp

---

## Status Pengembangan

- [x] **Tahap 0:** Pemeriksaan Lingkungan dan Perencanaan UTS.
- [x] **Tahap 1:** Inisialisasi Repositori Git dan Fondasi Proyek (Monorepo Laravel 11 & Vue 3 Vite).
- [x] **Tahap 2:** Containerization & Orkestrasi Docker Compose.
- [x] **Tahap 3:** Backend REST API & Autentikasi (Sanctum & PostgreSQL).
- [x] **Tahap 4:** Frontend Application (Vue 3, Vite, Axios) dengan branding Task Tracker.
- [x] **Tahap 5:** Continuous Integration (CI) Workflow GitHub Actions.
- [x] **Tahap 5.1:** Postman Collection v2.1 & Environment Export.
- [ ] **Tahap 6:** Continuous Delivery (CD) Pipeline ke GHCR.
- [ ] **Tahap 7:** Laporan Akhir UTS (PDF).

---

## Dokumentasi REST API

### 1. Daftar Endpoint

| Method | Endpoint | Deskripsi | Akses | Status Sukses |
|---|---|---|---|---|
| `GET` | `/api/health` | Status server dan konektivitas PostgreSQL | `auth:sanctum` | 200 |
| `POST` | `/api/login` | Otentikasi dan pembuatan token Sanctum | Publik | 200 |
| `POST` | `/api/logout` | Pencabutan (revoke) token aktif | `auth:sanctum` | 200 |
| `GET` | `/api/user` | Profil user terautentikasi | `auth:sanctum` | 200 |
| `GET` | `/api/tasks` | Daftar seluruh task | `auth:sanctum` | 200 |
| `POST` | `/api/tasks` | Membuat task baru | `auth:sanctum` | 201 |
| `GET` | `/api/tasks/{id}` | Detail task berdasarkan ID | `auth:sanctum` | 200 |
| `PUT` | `/api/tasks/{id}` | Memperbarui task | `auth:sanctum` | 200 |
| `DELETE` | `/api/tasks/{id}` | Menghapus task | `auth:sanctum` | 200 |

### 2. Kredensial User Uji Lokal (Seeder)
User default untuk pengujian lokal dibuat melalui:
```bash
docker compose exec backend php artisan db:seed
```
- **Email:** `test@example.com`
- **Password:** `password`

### 3. Contoh Pengujian dengan cURL
```bash
# 1. Login dan peroleh token
TOKEN=$(curl -s -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}' | grep -o '"token":"[^"]*' | cut -d'"' -f4)

# 2. Ambil daftar task
curl -H "Authorization: Bearer $TOKEN" http://localhost/api/tasks

# 3. Buat task baru
curl -X POST http://localhost/api/tasks \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"title":"Belajar Docker","description":"Praktikum UTS","status":"pending","due_date":"2026-10-31"}'

# 4. Logout (mencabut token)
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

## Panduan Menjalankan Layanan (Docker Compose)

### 1. Prasyarat & Konfigurasi Lingkungan
- Docker Desktop aktif pada host.
- Salin template environment pada root proyek:
  ```bash
  cp .env.example .env
  ```
- **Inisialisasi `APP_KEY` Unik:**
  Berkas `.env.example` sengaja dikosongkan (`APP_KEY=`) demi keamanan agar repositori bebas dari hardcoded secret. Docker Compose membaca nilai `APP_KEY` dari berkas `.env` pada host dan meneruskannya ke proses container backend. Sebelum atau sesudah container dijalankan, `APP_KEY` harus diisi:

  *Opsi A — Praktis sebelum container berjalan (menggunakan OpenSSL bawaan OS):*
  ```bash
  KEY="base64:$(openssl rand -base64 32)"
  sed -i '' "s|^APP_KEY=.*|APP_KEY=$KEY|" .env   # macOS
  # sed -i "s|^APP_KEY=.*|APP_KEY=$KEY|" .env   # Linux
  ```

  *Opsi B — Menggunakan Artisan setelah container berjalan:*
  1. Jalankan container:
     ```bash
     docker compose up -d --build
     ```
  2. Dapatkan kunci AES-256 acak dari container backend:
     ```bash
     docker compose exec backend php artisan key:generate --show
     ```
  3. Salin string yang dihasilkan (contoh: `base64:...`), buka berkas `.env` di host, lalu masukkan ke baris `APP_KEY=`.
  4. Muat ulang service backend agar environment variable baru terbaca oleh proses container:
     ```bash
     docker compose restart backend
     ```
  *(Catatan teknis: Perintah `php artisan key:generate` standar di dalam container hanya memperbarui berkas `/app/.env` di dalam container dan tidak otomatis mengubah berkas `.env` host. Karena Docker Compose menyuplai environment container dari berkas `.env` di host, kunci wajib disimpan pada `.env` host lalu backend dimuat ulang).*

### 2. Membangun dan Menjalankan Seluruh Layanan
Jika belum dijalankan pada langkah sebelumnya, jalankan perintah berikut pada root direktori:
```bash
docker compose up -d --build
```
Layanan yang akan berjalan:
- `db`: PostgreSQL 16 (Port `5432`, named volume `pgdata`)
- `backend`: Laravel 11 dengan FrankenPHP PHP 8.3 (Port `8000`)
- `frontend`: Nginx 1.27 melayani Vue 3 SPA & reverse proxy `/api` (Port `80`)

### 3. Memeriksa Status Layanan
```bash
docker compose ps
```

### 4. Menjalankan Migrasi Database & Seeder
Eksekusi migrasi tabel dan buat akun pengguna default (`test@example.com` / `password`):
```bash
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
```

### 5. Memeriksa Konektivitas dan Health Check
- **Frontend SPA (Nginx):** Buka di browser `http://localhost/` atau:
  ```bash
  curl -I http://localhost/
  ```
- **Reverse Proxy `/api/health`:**
  ```bash
  curl -i http://localhost/api/health
  ```
  *Respon yang diharapkan:* JSON dengan status `"database": "connected"`.
- **Backend Langsung (Debug):**
  ```bash
  curl -s http://localhost:8000/api/health
  ```

### 6. Menjalankan Pengujian Otomatis (Testing)
Eksekusi pengujian di dalam container backend:
```bash
docker compose exec backend php artisan test
```

### 7. Menghentikan Layanan
Untuk menghentikan layanan tanpa menghapus data volume:
```bash
docker compose down
```
*(Catatan: Jangan gunakan opsi `-v` agar data PostgreSQL pada volume `pgdata` tetap tersimpan).*

---

## Pengujian REST API dengan Postman

Tersedia berkas Postman Collection v2.1 dan Environment di dalam direktori `postman/`:
- **Collection:** `postman/Task-Tracker.postman_collection.json`
- **Environment:** `postman/Task-Tracker.postman_environment.json`

### Skenario Pengujian yang Dicakup:
1. **Autentikasi:**
   - `POST /api/login` (200 OK & otomatis menyimpan token ke variable `{{auth_token}}`).
   - `POST /api/login` (401 Unauthorized untuk kredensial tidak valid).
   - `GET /api/user` (200 OK profil pengguna terautentikasi dengan Bearer token).
2. **Health Check:**
   - `GET /api/health` (Status 200 OK terlindungi dengan Bearer token).
3. **CRUD Tasks:**
   - `GET /api/tasks` (200 OK daftar task dengan Bearer token).
   - `POST /api/tasks` (201 Created pembuatan task baru & menyimpan ID ke `{{task_id}}`).
   - `POST /api/tasks` (422 Unprocessable Content untuk input tanpa judul wajib).
   - `GET /api/tasks/{{task_id}}` (200 OK detail task).
   - `PUT /api/tasks/{{task_id}}` (200 OK pembaruan task).
   - `DELETE /api/tasks/{{task_id}}` (200 OK penghapusan task).
4. **Keamanan & Otorisasi:**
   - `GET /api/tasks` tanpa token (401 Unauthorized / Unauthenticated).
   - `GET /api/health` tanpa token (401 Unauthorized / Unauthenticated).
   - `POST /api/logout` (200 OK pencabutan token aktif).

### Petunjuk Penggunaan di Postman:
1. Buka aplikasi Postman, klik **Import**, lalu pilih kedua berkas JSON dari folder `postman/`.
2. Aktifkan environment **Task Tracker - Local Environment** pada pemilih environment (kanan atas Postman).
3. Jalankan request `Login - Success (200 OK)` terlebih dahulu. Token Sanctum akan otomatis disimpan ke variabel `{{auth_token}}`.
4. Jalankan pengujian request lainnya secara berurutan atau menggunakan **Collection Runner**.
