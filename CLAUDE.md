# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Repository Overview

This repo contains two independent web applications for **BIM University** student admission (PMB — Penerimaan Mahasiswa Baru):

| Directory | Stack | Purpose |
|-----------|-------|---------|
| `ods/` | Django 4.2 + MSSQL | PMB Terpadu (Raport/Beasiswa/Umum) — production at `ods.bim.ac.id` |
| `pmb/` | Laravel 11 + MySQL | PMB BIM University (KIP path, multi-role admin) |

These are separate projects with separate dependencies, databases, and deployment pipelines. Work in one does not affect the other.

---

## `ods/` — Django PMB Terpadu

### Local Development

Requires Python 3.9+ and ODBC Driver 17 for SQL Server.

```bash
cd ods
cp .env.example .env          # fill SECRET_KEY and DB_PASSWORD
pip install -r requirements.txt
py manage.py migrate
py manage.py createsuperuser
py manage.py load_wilayah     # seed Provinsi & Kota
py manage.py runserver 8080
```

### Key Commands

```bash
py manage.py test                          # run all tests
py manage.py test panel.tests              # run single app tests
py manage.py migrate --check               # verify pending migrations
py manage.py collectstatic --noinput       # production static files
```

### Architecture

```
pmb_terpadu/   settings, urls, wsgi — project config
core/          shared models: Prodi, Kota, Provinsi, SiteSetting + validators + storages
pendaftaran/   Pendaftar model (OneToOne with User), data diri, berkas daftar-ulang
raport/        BerkasRaport, NilaiRaport, PrestasiRaport
beasiswa/      BerkasBeasiswa, PrestasiBeasiswa
umum/          CBT registration
panel/         Staff-only views: validasi, kelulusan, registrasi (NIM generation), PDF output
templates/     Metronic theme HTML (not split per app)
```

**Key design decisions:**
- `core.models.SiteSetting` is a singleton (pk=1) — all PDF documents (KTM, sertifikat, bukti registrasi) pull university name/logo/pejabat from it.
- NIM is auto-generated in panel as `kode_prodi + YY + 0 + 3-digit-sequence`.
- `core.storages.TolerantCompressedManifestStaticFilesStorage` suppresses fatal errors from missing sourcemaps during `collectstatic`.
- WhiteNoise middleware is only active when `DEBUG=False`.
- Security headers (SSL redirect, HSTS, secure cookies) are only enforced in production.

**File upload validation** is in `core/validators.py`: documents max 5 MB (PDF/Word), images max 2 MB (JPG/PNG). Both `FileExtensionValidator` and size validators are applied to every FileField/ImageField.

### Environment Variables (`.env`)

| Variable | Notes |
|----------|-------|
| `SECRET_KEY` | Required — raises `RuntimeError` if missing |
| `DEBUG` | `True`/`False` — controls WhiteNoise, security headers |
| `DB_PASSWORD` | MSSQL password for `10.3.11.70,1433` |
| `ALLOWED_HOSTS` | Comma-separated |
| `CSRF_TRUSTED_ORIGINS` | Comma-separated — needed for HTTPS proxy setups |

### Production / Deployment

Deployed to MicroK8s at `10.3.11.53`. Code lives on NFS (`10.3.11.52:/mnt/nfs/pmb_terpadu`) mounted at `/app`. The shared Docker image `haunans/my-akademik-image:latest` (also used by myakademik and pmb_raport) already contains ODBC Driver 17 and a self-signed cert for gunicorn HTTPS. Code is not baked into the image — pod startup runs `pip install` from NFS.

CI/CD: GitLab CI with runner tag `pmb-terpadu-runner`; pushes to `main` trigger a `kubectl rollout restart`. `k8s-secret.yaml` (contains plaintext `SECRET_KEY` + `DB_PASSWORD`) is applied manually and **not committed**.

---

## `pmb/` — Laravel PMB BIM University

> **All Composer/Artisan/npm commands must run from inside `pmb/app/`.**

### Local Development

Requires PHP 8.2+, Composer, Node.js. SQLite by default locally.

```bash
cd pmb/app
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
npm run dev          # Vite watch
php artisan serve    # http://localhost:8000
```

### Key Commands

```bash
php artisan test                           # run all tests
php artisan test --filter StudentTest      # run single test class
php artisan migrate:fresh --seed           # reset DB with seeders
php artisan permission:cache-reset         # after role/permission changes
npm run build                              # production asset build
```

### Architecture

**Routes split by role** (`routes/`):
- `web.php` — student: registration, document upload, daftar ulang, profile
- `admrektorat.php` — verify students, change status, exam data, Excel export
- `superadmin.php` — roles/permissions, master data, app settings
- `pimpinan.php` — read-only dashboards

**Key models** (`app/Models/`):
- `Student` — implements `HasMedia` (spatie/medialibrary); holds status (`0`=pending, `1`=verified, `2`=diterima prodi1, `3`=diterima prodi2), linked to `User` one-to-one
- `Biodata` — detailed personal data (separate from Student to keep Student table lean)
- `StudentDocument` — polymorphic-style berkas per student
- `JalurPendaftaran` / `Program` / `Prodi` — master data managed by superadmin
- `PromoCode` / `PromoCodeUsage` — promo code validation during registration
- `RegisterPeriod` — controls active registration window
- `Setting` — singleton app config (similar to ODS's SiteSetting)

**Auth & permissions:** Laravel Breeze for auth, `spatie/laravel-permission` for roles. Role checks are in route middleware. `AppServiceProvider.php` (in repo root, not `app/`) is a deployment artifact — the real one is at `app/app/Providers/`.

**PDF generation:** `barryvdh/laravel-dompdf` — used for registration cards and documents in the daftar-ulang flow.

**Excel export:** `maatwebsite/excel` via Exports classes in `app/Exports/`.

### Production / Deployment

Docker image: `haunans/pmbbim:latest` based on `php:8.2-apache`. Application files and uploads are stored on NFS (`pmbbim-nfspvc`, 10Gi, `ReadWriteMany`) — all pod replicas share the same storage. Kubernetes manifests are in `k8s/`.

Helper Python scripts in `pmb/` root (`upload_to_nfs.py`, `chown_nfs.py`, `fix_env_nfs.py`, `deploy_setting.py`, `reset_admin_password.py`) assist with NFS setup and production config.

---

## What Is NOT Committed

Both projects intentionally exclude:
- `.env` files (credentials)
- `k8s-secret.yaml` / production env files (plaintext secrets)
- `storage/app/public/` student uploads (PII: akta lahir, rapor, KTP)
- `staticfiles/` / `public_html/` build outputs
- Database dumps (`*.sql`, `*.bak`)
