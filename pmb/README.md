# PMB BIM University

Sistem **Penerimaan Mahasiswa Baru (PMB)** BIM University — aplikasi pendaftaran
mahasiswa baru berbasis web, mulai dari pendaftaran awal, jalur KIP, daftar ulang,
verifikasi berkas, hingga manajemen oleh admin rektorat dan superadmin.

Dibangun dengan **Laravel 11 / PHP 8.2**, di-deploy menggunakan **Docker** dan
**Kubernetes** dengan penyimpanan berkas via NFS.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Peran Pengguna](#peran-pengguna)
- [Struktur Proyek](#struktur-proyek)
- [Menjalankan Secara Lokal](#menjalankan-secara-lokal)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Deployment](#deployment)
- [Catatan Keamanan](#catatan-keamanan)

---

## Fitur Utama

- **Pendaftaran mahasiswa baru** — calon mahasiswa mendaftar, memilih program studi
  dan jalur pendaftaran, serta mengunggah berkas.
- **Jalur KIP** — alur pendaftaran khusus penerima KIP (Kartu Indonesia Pintar)
  dengan dashboard verifikasi tersendiri.
- **Kode Promo** — validasi dan penggunaan kode promo saat pendaftaran
  (`PromoCode`, `PromoCodeUsage`).
- **Daftar ulang** — alur multi-tahap (biodata → administrasi → pembayaran →
  beasiswa) lengkap dengan cetak kartu (PDF).
- **Verifikasi berkas & ubah status** oleh admin rektorat.
- **Data ujian** (`ExamData`) per mahasiswa.
- **Ekspor data** mahasiswa & KIP ke Excel.
- **Manajemen akses** (role & permission) oleh superadmin.
- **Master data** — jalur pendaftaran, program, program studi, periode pendaftaran,
  dan pengaturan aplikasi.

## Teknologi

| Komponen          | Detail                                                        |
| ----------------- | ------------------------------------------------------------- |
| Framework         | Laravel 11 (PHP ^8.2)                                         |
| Autentikasi       | Laravel Breeze                                                |
| Role & Permission | spatie/laravel-permission                                     |
| Media / Berkas    | spatie/laravel-medialibrary                                   |
| PDF               | barryvdh/laravel-dompdf                                       |
| Excel             | maatwebsite/excel                                             |
| Frontend          | Blade, Vite, Tailwind CSS                                     |
| Database          | MySQL (produksi) · SQLite (default lokal)                     |
| Deployment        | Docker (php:8.2-apache) + Kubernetes (NFS, 2 replica)         |

## Peran Pengguna

Akses dibedakan berdasarkan role (lihat `routes/`):

- **student** — pendaftaran, unggah dokumen, daftar ulang, profil. (`routes/web.php`)
- **admrektorat** — verifikasi mahasiswa, ubah status, data ujian, ekspor.
  (`routes/admrektorat.php`)
- **superadmin** — manajemen akses, master data, dan pengaturan aplikasi.
  (`routes/superadmin.php`)

## Struktur Proyek

```
pmb/
├── app/                # Aplikasi Laravel (root utama)
│   ├── app/            #   Models, Controllers, Exports, Middleware
│   ├── routes/         #   web, auth, admrektorat, superadmin
│   ├── resources/      #   Blade views, assets
│   ├── database/       #   migrations, seeders, factories
│   └── .env.example    #   contoh konfigurasi
├── k8s/                # Manifest Kubernetes
│   ├── portainer-stack.yaml   # Deployment, Service, PVC (NFS)
│   ├── job-setup.yaml         # Job inisialisasi
│   └── nginx-pmbbim.conf      # Konfigurasi reverse proxy
├── public_html/        # Build/aset publik untuk deployment
├── Dockerfile          # Image php:8.2-apache + ekstensi PHP
└── *.py                # Skrip bantu deploy (NFS, reset password, dll.)
```

> **Catatan:** root aplikasi Laravel berada di dalam folder `app/`. Jalankan
> seluruh perintah `composer`, `php artisan`, dan `npm` dari dalam `app/`.

## Menjalankan Secara Lokal

Prasyarat: PHP 8.2+, Composer, Node.js, dan (opsional) MySQL.

```bash
cd app

# 1. Install dependensi
composer install
npm install

# 2. Konfigurasi environment
cp .env.example .env
php artisan key:generate

# 3. Siapkan database & jalankan migrasi
#    Default .env.example memakai SQLite:
touch database/database.sqlite
php artisan migrate --seed

# 4. Symlink storage agar berkas unggahan bisa diakses
php artisan storage:link

# 5. Jalankan aplikasi
npm run dev          # build aset (mode watch)
php artisan serve    # http://localhost:8000
```

## Konfigurasi Environment

Salin `app/.env.example` menjadi `app/.env` lalu sesuaikan. Variabel penting:

```dotenv
APP_NAME="PMB BIM University"
APP_URL=http://localhost:8000

# SQLite (lokal) — atau ganti ke MySQL untuk produksi:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bim_pmb
DB_USERNAME=root
DB_PASSWORD=
```

> File `.env` **tidak** disertakan di repository (berisi kredensial). Gunakan
> `.env.example` sebagai acuan. Untuk produksi tersedia juga `app/.env.k8s`.

## Deployment

Aplikasi dikemas sebagai image Docker (`Dockerfile`) berbasis `php:8.2-apache`
dengan ekstensi `pdo_mysql`, `gd`, `zip`, `intl`, `bcmath`, `gmp`, dll.

```bash
# Build & push image
docker build -t haunans/pmbbim:latest .
docker push haunans/pmbbim:latest
```

Deployment Kubernetes (lihat `k8s/portainer-stack.yaml`):

- **StorageClass NFS** (`nfs.csi.k8s.io`) — berkas aplikasi & unggahan disimpan di
  share NFS dan di-mount ke seluruh pod (ReadWriteMany).
- **Deployment** `pmbbim` — 2 replica, strategi RollingUpdate.
- **PVC** `pmbbim-nfspvc` (10Gi).
- **Job** `job-setup.yaml` untuk inisialisasi (migrasi, dsb.).

```bash
kubectl apply -f k8s/portainer-stack.yaml
kubectl apply -f k8s/job-setup.yaml
```

Skrip Python pembantu deployment tersedia di root: `upload_to_nfs.py`,
`chown_nfs.py`, `fix_env_nfs.py`, `deploy_setting.py`, dan
`reset_admin_password.py`.

## Catatan Keamanan

- **Berkas unggahan calon mahasiswa** (akta lahir, rapor, dsb. di
  `app/storage/app/public/`) berisi **data pribadi (PII)** dan sengaja
  **tidak di-commit** ke repository (lihat `.gitignore`).
- **Dump database** (`*.sql`, `*.bak`) dan file `.env` juga dikecualikan dari
  repository.
- Jangan pernah meng-commit kredensial atau data pribadi mahasiswa.

---

© BIM University — Sistem Penerimaan Mahasiswa Baru.
