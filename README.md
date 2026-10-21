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
- [ ] **Tahap 2:** Containerization & Orkestrasi Docker Compose.
- [ ] **Tahap 3:** Backend REST API & Autentikasi (Sanctum & PostgreSQL).
- [ ] **Tahap 4:** Frontend Application (Vue 3, Vite, Tailwind CSS, Axios).
- [ ] **Tahap 5:** Pengujian Terintegrasi End-to-End.
- [ ] **Tahap 6:** CI/CD Pipeline (GitHub Actions & GHCR).
- [ ] **Tahap 7:** Dokumentasi (Postman Collection) & Laporan UTS.
