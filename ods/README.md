# PMB Terpadu

Aplikasi Pendaftaran Mahasiswa Baru terpadu untuk tiga jalur: **Raport, Beasiswa, Umum** dalam satu codebase. Menggantikan `pmb_raport` (https://ods.bim.ac.id) yang hanya cover jalur raport.

Dibangun dengan **Django 4.2 + SQL Server (MSSQL)**. Di-deploy ke **MicroK8s** via GitLab CI/CD.

- **Production:** https://ods.bim.ac.id *(pengganti pmb_raport)*
- **GitLab:** https://gitlab.ums.ac.id/akademik/pmb_terpadu

---

## Fitur

### Calon Mahasiswa
- Pendaftaran akun per jalur (Raport / Beasiswa / Umum)
- Login / Logout, ganti password
- Isi data diri, alamat, sekolah, orang tua
- Upload foto & scan KTP
- Berkas per jalur:
  - **Raport**: upload raport + nilai mata pelajaran + sertifikat prestasi (maks 3)
  - **Beasiswa**: formulir, penghasilan, rekomendasi, raport, prestasi, TOEFL
  - **Umum**: pendaftaran CBT
- Pantau hasil seleksi real-time
- Registrasi ulang setelah dinyatakan lulus (upload dokumen)
- Cetak bukti registrasi, sertifikat kelulusan, dan KTM (setelah NIM terbit)

### Admin / Panitia (Panel)
- Login staff (`is_staff=True`) di `/panel/login/`
- Dashboard statistik (total pendaftar per jalur, validasi, lulus, registrasi)
- Validasi berkas pendaftar per jalur (status, catatan, nilai)
- Proses kelulusan bulk (passing grade raport, prodi diterima)
- Proses registrasi + generate NIM otomatis (`kode_prodi + YY + 0 + 3-digit`)
- Cetak dokumen PDF (sertifikat lulus, bukti registrasi, KTM) via ReportLab
- Pengaturan dinamis universitas (nama, logo, favicon, kop surat, pejabat)

### Security hardening
- Open-redirect guard di login panel & pendaftaran (`url_has_allowed_host_and_scheme`)
- `FileExtensionValidator` + ukuran max di semua FileField/ImageField (dokumen 5MB, gambar 2MB)
- WhiteNoise `CompressedManifestStaticFilesStorage` (conditional by `DEBUG`)
- Security headers saat `DEBUG=False`: SSL redirect, HSTS, secure cookies, X-Frame-Options DENY

---

## Struktur Aplikasi

```
pmb_terpadu/       # Project config (settings, urls, wsgi)
core/              # Model bersama: Prodi, Kota, Provinsi, SiteSetting + validators
pendaftaran/       # Akun cama, data diri, Registrasi, berkas daftar-ulang
raport/            # Jalur raport: berkas raport, nilai, prestasi
beasiswa/          # Jalur beasiswa: berkas + jenis beasiswa + prestasi
umum/              # Jalur umum: pendaftaran CBT
panel/             # Panitia/admin: validasi, kelulusan, registrasi, PDF, pengaturan
templates/         # HTML (Metronic theme)
static/            # Aset vendored (Bootstrap, datatables, font-awesome)
```

## Struktur URL

| Path | Keterangan |
|------|------------|
| `/` | Halaman awal / redirect ke dashboard |
| `/daftar/?jalur=raport\|beasiswa\|umum` | Form pendaftaran akun |
| `/login/` | Login calon mahasiswa |
| `/dashboard/` | Dashboard pendaftar |
| `/data-diri/`, `/upload-ktp/`, `/data-sekolah/`, `/data-ortu/` | Isi data |
| `/raport/nilai/`, `/raport/berkas/` | Jalur raport |
| `/beasiswa/berkas/`, `/beasiswa/prestasi/tambah/` | Jalur beasiswa |
| `/umum/daftar/` | Jalur umum |
| `/pantau-status/` | Pantau hasil seleksi |
| `/registrasi/` | Upload berkas registrasi + cetak dokumen |
| `/panel/login/` | Login panitia |
| `/panel/dashboard/` | Dashboard admin |
| `/panel/validasi/` | Validasi berkas per jalur |
| `/panel/kelulusan/` | Proses kelulusan (bulk passing grade) |
| `/panel/registrasi/` | Proses registrasi & NIM |
| `/panel/pengaturan/` | Setting universitas |
| `/admin/` | Django admin (superuser) |

---

## Setup Lokal

### Prasyarat
- Python 3.9+ (direkomendasikan 3.12)
- ODBC Driver 17 for SQL Server
- Akses ke SQL Server `10.3.11.70,1433`

### Langkah

```bash
git clone https://gitlab.ums.ac.id/akademik/pmb_terpadu.git
cd pmb_terpadu
cp .env.example .env
# Edit .env, isi DB_PASSWORD dan SECRET_KEY

pip install -r requirements.txt
py manage.py migrate
py manage.py createsuperuser
py manage.py load_wilayah      # seed Provinsi & Kota
py manage.py runserver 8000
```

Akses di http://localhost:8000/.

---

## Setup Production (MicroK8s)

### Koordinat infrastruktur
- **Cluster MicroK8s / Portainer**: `10.3.11.53`
- **NFS storage**: `10.3.11.52` → export `/mnt/nfs/pmb_terpadu`
- **Nginx reverse proxy (SSL termination)**: `10.3.11.60` → `https://10.3.11.53:817`
- **MSSQL**: `10.3.11.70,1433` (DB `pmb_terpadu`, user `wuryanto`)

### 1. Siapkan NFS
```bash
ssh 10.3.11.52
sudo mkdir -p /mnt/nfs/pmb_terpadu/{staticfiles,media,logs}
sudo chmod -R 777 /mnt/nfs/pmb_terpadu/{staticfiles,media,logs}
```

### 2. Apply PV + PVC
```bash
microk8s kubectl apply -f /mnt/nfs/pmb_terpadu/k8s-pv.yaml
```

### 3. Buat Secret dari template
```bash
cp /mnt/nfs/pmb_terpadu/k8s-secret.yaml.example /mnt/nfs/pmb_terpadu/k8s-secret.yaml
# Edit: ganti SECRET_KEY + DB_PASSWORD
microk8s kubectl apply -f /mnt/nfs/pmb_terpadu/k8s-secret.yaml
```

Generate `SECRET_KEY`:
```bash
py -c "from django.core.management.utils import get_random_secret_key; print(get_random_secret_key())"
```

### 4. Apply Service + Deployment
```bash
microk8s kubectl apply -f /mnt/nfs/pmb_terpadu/k8s-service.yaml
microk8s kubectl apply -f /mnt/nfs/pmb_terpadu/k8s-deployment.yaml
```

### 5. Migrasi DB + superuser
```bash
microk8s kubectl exec -it deploy/pmb-terpadu-deployment -- python /app/manage.py migrate
microk8s kubectl exec -it deploy/pmb-terpadu-deployment -- python /app/manage.py createsuperuser
microk8s kubectl exec -it deploy/pmb-terpadu-deployment -- python /app/manage.py load_wilayah
```

### 6. Konfigurasi Nginx (10.3.11.60)
```nginx
# /etc/nginx/sites-enabled/ods.bim.ac.id
location / {
    proxy_pass https://10.3.11.53:817;
    proxy_set_header Host $host;
    proxy_set_header X-Forwarded-Proto https;
    proxy_set_header X-Real-IP $remote_addr;
}
# Opsional: serve media langsung (lebih efisien)
location /media/ {
    alias /mnt/nfs/pmb_terpadu/media/;
    expires 30d;
}
```

---

## CI/CD (GitLab)

Auto-deploy ke MicroK8s setiap push ke branch `main` via **RollingUpdate** (zero-downtime).

### Prasyarat
- GitLab Runner tag `pmb-terpadu-runner` terdaftar di server cluster
- Variable di GitLab → Settings → CI/CD → Variables:

| Variable | Nilai |
|---|---|
| `APP_PATH` | `/mnt/nfs/pmb_terpadu` |

### Alur deploy
1. Pull kode terbaru dari GitLab ke NFS (`$APP_PATH`)
2. `kubectl apply -f k8s-deployment.yaml`
3. `kubectl rollout restart deployment/pmb-terpadu-deployment`
4. Tunggu `readinessProbe /login/` berhasil sebelum matikan pod lama

### Zero-downtime deploy
- `maxUnavailable: 0` — pod lama tidak dimatikan sebelum pod baru siap
- `maxSurge: 1` — 1 pod ekstra dibuat saat deploy
- `readinessProbe` — cek `GET /login/` HTTPS mulai 60 detik setelah startup

---

## Arsitektur

```
Cloudflare / DNS (ods.bim.ac.id)
       │
Nginx Reverse Proxy (10.3.11.60)
  - SSL termination (Let's Encrypt)
  - proxy_pass https://10.3.11.53:817
       │
MicroK8s LoadBalancer Service (10.3.11.53:817)
       │
Deployment pmb-terpadu (2–6 replica, HPA)
  - Image: haunans/my-akademik-image:latest (shared)
  - Startup: pip install → collectstatic → gunicorn HTTPS
       │
NFS PVC pmb-terpadu-nfs
  10.3.11.52:/mnt/nfs/pmb_terpadu → /app
       │
SQL Server (10.3.11.70:1433) DB: pmb_terpadu
```

### Kenapa pakai shared image `haunans/my-akademik-image`

Image ini juga dipakai myakademik & pmb_raport. Sudah include:
- ODBC Driver 17 for SQL Server
- Python 3.9 + build tools
- Self-signed cert untuk gunicorn HTTPS di `/etc/ssl/certs/cert.pem`

Kode pmb_terpadu tidak di-bake ke image — di-mount dari NFS PVC ke `/app`. Startup pod `pip install -r /app/requirements.txt` dari NFS, lalu collectstatic + gunicorn.

---

## Catatan Operasional

### File konfigurasi yang TIDAK di-commit
- `.env` — kredensial dev lokal (ada di `.gitignore`)
- `k8s-secret.yaml` — berisi `SECRET_KEY` + `DB_PASSWORD` plain-text (apply manual di server)

### File ter-ignore
- `staticfiles/` — dihasilkan `collectstatic` saat pod start
- `media/` — upload user (foto, berkas), persisten di NFS
- `logs/` — output RotatingFileHandler
- `__pycache__/`, `*.pyc`

### Troubleshooting

**Logo tidak muncul di production**
`/media/` di-serve via Django (`urls.py` kondisi `not DEBUG`). Kalau ingin via Nginx langsung, tambah `location /media/` di Nginx config.

**`collectstatic` fail karena sourcemap missing**
Sudah di-handle oleh `core.storages.TolerantCompressedManifestStaticFilesStorage` — warning tapi tidak fatal.

**`mkdir: Permission denied /app/staticfiles`**
NFS export pakai `root_squash`. Fix: `chmod -R 777 /mnt/nfs/pmb_terpadu/{staticfiles,media,logs}` di server NFS.

**Pod stuck pada `Pending`**
Cek PVC `pmb-terpadu-nfs` dan Secret `pmb-terpadu-secret` sudah di-apply:
```bash
microk8s kubectl get pvc,secret -l app=pmb-terpadu
microk8s kubectl describe pod -l app=pmb-terpadu | tail -30
```

---

## Lisensi

Internal use — Universitas Muhammadiyah Surakarta / BIM.
