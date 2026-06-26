"""Migrasi data pendaftar dari pmb.bim.ac.id (Laravel/MySQL) ke pmb_terpadu.

Sumber:
  MySQL 10.3.11.24 db `pmbbimac_pmb` — tables: users, students, biodatas,
  prodis, programs, jalur_pendaftarans.

Target:
  MSSQL 10.3.11.70 db `pmb_terpadu` — models: User, Pendaftar, Sekolah, Ortu,
  Registrasi.

Konfigurasi via env:
  OLD_DB_HOST / OLD_DB_PORT / OLD_DB_USER / OLD_DB_PASSWORD / OLD_DB_NAME

Usage:
  py manage.py migrate_bim_2026 --dry-run
  py manage.py migrate_bim_2026 --from 2026-01-01 --to 2026-12-31
  py manage.py migrate_bim_2026 --limit 10           # test batch kecil
  py manage.py migrate_bim_2026 --unusable-password  # password di-disable,
                                                     # cama reset via email

Idempotent: skip pendaftar yang email-nya sudah ada di auth_user atau NIK-nya
sudah ada di tabel pendaftar. Rerun aman.
"""
import os
import re
from datetime import date

from django.core.management.base import BaseCommand, CommandError
from django.db import transaction
from django.contrib.auth.models import User

from core.models import Prodi
from pendaftaran.models import (
    Pendaftar, Sekolah, Ortu, Registrasi, generate_no_daftar,
)
from raport.models import RaportBerkas
from beasiswa.models import BeasiswaDaftar
from umum.models import UmumDaftar


# ── Mapping Laravel → Django ──────────────────────────────────────────────────

JALUR_MAP = {
    2: 'beasiswa',   # Beasiswa Parsial
    5: 'umum',       # Umum/Reguler
    6: 'beasiswa',   # KIP Kuliah
}

PROGRAM_KELAS_MAP = {
    1: 'reguler',        # Regular Class
    2: 'internasional',  # International Class
    3: 'karyawan',       # BIM Online (Kelas Karyawan)
}

HUBUNGAN_MAP = {
    'ayah': 'ayah', 'bapak': 'ayah', 'father': 'ayah',
    'ibu': 'ibu', 'mother': 'ibu', 'bunda': 'ibu',
}

# Alias nama prodi Laravel → Django (kalau ejaan beda).
PRODI_NAME_ALIAS = {
    'teknologi informasi': 'teknologi informatika',
}


class Command(BaseCommand):
    help = 'Migrasi data pendaftar pmb.bim.ac.id (Laravel MySQL) ke pmb_terpadu.'

    def add_arguments(self, parser):
        parser.add_argument('--from', dest='from_date', default='2026-01-01',
                            help='Filter register_at >= (YYYY-MM-DD, default 2026-01-01)')
        parser.add_argument('--to', dest='to_date', default='2026-12-31',
                            help='Filter register_at <= (YYYY-MM-DD, default 2026-12-31)')
        parser.add_argument('--limit', type=int, default=0,
                            help='Batasi jumlah record yang diproses (0 = semua)')
        parser.add_argument('--dry-run', action='store_true',
                            help='Preview tanpa menulis ke DB target')
        parser.add_argument('--unusable-password', action='store_true',
                            help='Set password tidak bisa dipakai (paksa reset). '
                                 'Default: password = NIK 16 digit.')
        parser.add_argument('--force-refresh', action='store_true',
                            help='Hapus pendaftar existing (match by email atau NIK) '
                                 'lalu re-migrate. Cascade delete ke User, Sekolah, '
                                 'Ortu, RaportBerkas/BeasiswaDaftar/UmumDaftar, Registrasi.')
        parser.add_argument('--allow-empty-nik', action='store_true',
                            help='Migrate juga cama yang NIK-nya kosong di biodata '
                                 'Laravel (belum isi data). NIK disimpan "" di Django, '
                                 'cama diminta lengkapi data via dashboard.')
        parser.add_argument('--fallback-password', default='pmb2026',
                            help='Password default kalau NIK kosong (default: pmb2026). '
                                 'Diabaikan jika --unusable-password di-set.')

    def handle(self, *args, **opts):
        try:
            import pymysql
            from pymysql.cursors import DictCursor
        except ImportError:
            raise CommandError('PyMySQL belum terinstall. Jalankan: pip install PyMySQL')

        cfg = {
            'host':     os.environ.get('OLD_DB_HOST'),
            'port':     int(os.environ.get('OLD_DB_PORT', '3306')),
            'user':     os.environ.get('OLD_DB_USER'),
            'password': os.environ.get('OLD_DB_PASSWORD'),
            'database': os.environ.get('OLD_DB_NAME', 'pmbbimac_pmb'),
        }
        missing = [k for k in ('host', 'user', 'password') if not cfg[k]]
        if missing:
            raise CommandError(
                f'Env var belum di-set: {", ".join("OLD_DB_" + k.upper() for k in missing)}'
            )

        self.dry_run = opts['dry_run']
        self.use_unusable_password = opts['unusable_password']
        self.force_refresh = opts['force_refresh']
        self.allow_empty_nik = opts['allow_empty_nik']
        self.fallback_password = opts['fallback_password']

        self.stdout.write(self.style.NOTICE(
            f'Koneksi ke MySQL {cfg["host"]}:{cfg["port"]}/{cfg["database"]} ...'
        ))
        conn = pymysql.connect(
            host=cfg['host'], port=cfg['port'],
            user=cfg['user'], password=cfg['password'],
            database=cfg['database'], charset='utf8mb4',
            cursorclass=DictCursor, connect_timeout=10,
        )

        try:
            self._run(conn, opts)
        finally:
            conn.close()

    def _run(self, conn, opts):
        sql = """
            SELECT
                s.id              AS student_id,
                s.user_id         AS user_id,
                s.name            AS student_name,
                s.phone_number    AS phone_number,
                s.referensi       AS referensi,
                s.prodi1_id       AS prodi1_id,
                s.prodi2_id       AS prodi2_id,
                s.program_id      AS program_id,
                s.status          AS status,
                s.jalur_pendaftaran_id AS jalur_id,
                s.register_at     AS register_at,
                u.email           AS email,
                u.name            AS user_name,
                p1.name           AS prodi1_name,
                p2.name           AS prodi2_name,
                b.name            AS bio_name,
                b.nomor_hp        AS bio_hp,
                b.alamat          AS bio_alamat,
                b.tanggal_lahir   AS bio_tgl_lahir,
                b.nik             AS bio_nik,
                b.tempat_lahir    AS bio_tempat_lahir,
                b.jenis_kelamin   AS bio_jk,
                b.asal_sekolah    AS bio_sekolah,
                b.nisn            AS bio_nisn,
                b.nama_orangtua   AS bio_ortu_nama,
                b.nomor_hp_orangtua AS bio_ortu_hp,
                b.hubungan        AS bio_ortu_hubungan,
                b.parent_work     AS bio_ortu_pekerjaan,
                b.parent_income   AS bio_ortu_penghasilan
            FROM students s
            LEFT JOIN users   u  ON u.id  = s.user_id
            LEFT JOIN prodis  p1 ON p1.id = s.prodi1_id
            LEFT JOIN prodis  p2 ON p2.id = s.prodi2_id
            LEFT JOIN biodatas b ON b.student_id = s.id
            WHERE DATE(s.register_at) BETWEEN %s AND %s
            ORDER BY s.id
        """
        params = [opts['from_date'], opts['to_date']]
        if opts['limit']:
            sql += ' LIMIT %s'
            params.append(opts['limit'])

        with conn.cursor() as cur:
            cur.execute(sql, params)
            rows = cur.fetchall()

        self.stdout.write(self.style.NOTICE(
            f'Ditemukan {len(rows)} pendaftar di rentang '
            f'{opts["from_date"]} .. {opts["to_date"]}.'
        ))

        # Print distribusi jalur & status supaya user bisa verify mapping
        from collections import Counter
        jalur_dist = Counter(r.get('jalur_id') for r in rows)
        status_dist = Counter(r.get('status') for r in rows)
        prog_dist = Counter(r.get('program_id') for r in rows)
        self.stdout.write('  Distribusi jalur_id Laravel: ' + str(dict(jalur_dist)))
        self.stdout.write('  Distribusi status Laravel:   ' + str(dict(status_dist)))
        self.stdout.write('  Distribusi program_id:       ' + str(dict(prog_dist)))

        if self.dry_run:
            self.stdout.write(self.style.WARNING('[DRY RUN] tidak menulis ke DB target.'))

        # Pre-load Prodi cache — match primary by id (Laravel prodis.id cocok dengan
        # Django Prodi.pk berdasarkan urutan seed), fallback by nama (case-insensitive)
        # untuk handle beda ejaan (mis. "Teknologi Informasi" vs "Teknologi Informatika").
        all_prodi = list(Prodi.objects.all())
        if not all_prodi:
            raise CommandError('Tabel Prodi Django kosong. Seed Prodi dulu sebelum migrasi.')
        self.prodi_by_id = {p.pk: p for p in all_prodi}
        self.prodi_by_name = {p.nama.strip().lower(): p for p in all_prodi}

        migrated = skipped_existing = skipped_invalid = errored = 0
        for i, row in enumerate(rows, start=1):
            tag = f'[{i}/{len(rows)} #{row["student_id"]}]'
            email = (row.get('email') or '').strip().lower()
            nik_raw = row.get('bio_nik') or ''
            nik = re.sub(r'\D', '', str(nik_raw))[:16]

            if not email:
                self._warn(f'{tag} SKIP: email kosong')
                skipped_invalid += 1
                continue
            if len(nik) != 16:
                if not self.allow_empty_nik:
                    self._warn(f'{tag} SKIP: NIK tidak valid ({nik_raw!r})')
                    skipped_invalid += 1
                    continue
                # --allow-empty-nik: NIK kosong diizinkan
                nik = ''

            existing_user = User.objects.filter(username=email).first()
            # Dedup by NIK hanya kalau NIK tidak kosong (empty string boleh duplikat).
            existing_pend = Pendaftar.objects.filter(NIK=nik).first() if nik else None

            if existing_user or existing_pend:
                if not self.force_refresh:
                    self._info(f'{tag} SKIP: email/NIK sudah ada (pakai --force-refresh untuk re-migrate)')
                    skipped_existing += 1
                    continue
                if not self.dry_run:
                    # Hapus data existing — cascade ke semua relasi
                    if existing_pend:
                        existing_pend.user.delete()  # cascade → Pendaftar + Sekolah + Ortu + ...
                    elif existing_user:
                        existing_user.delete()
                self._warn(f'{tag} REFRESH: hapus data lama {email} / {nik}')

            try:
                if not self.dry_run:
                    with transaction.atomic():
                        self._create_pendaftar(row, email, nik)
                self._ok(f'{tag} OK: {email} ({nik})')
                migrated += 1
            except Exception as e:
                self._err(f'{tag} ERROR: {e}')
                errored += 1

        self.stdout.write(self.style.SUCCESS(
            f'\nSelesai. migrated={migrated} skipped_existing={skipped_existing} '
            f'skipped_invalid={skipped_invalid} errored={errored}'
        ))

    def _create_pendaftar(self, row, email, nik):
        jalur = JALUR_MAP.get(row.get('jalur_id') or -1, 'umum')
        kelas = PROGRAM_KELAS_MAP.get(row.get('program_id') or -1, '')

        prodi1 = self._find_prodi(row.get('prodi1_id'), row.get('prodi1_name'))
        prodi2 = self._find_prodi(row.get('prodi2_id'), row.get('prodi2_name'))
        if not prodi1:
            raise ValueError(
                f'Prodi Laravel id={row.get("prodi1_id")} name={row.get("prodi1_name")!r} '
                f'tidak ditemukan di tabel Prodi Django (cek by id dan nama).'
            )

        nama = (row.get('bio_name') or row.get('student_name') or row.get('user_name') or '').strip()
        no_hp = (row.get('bio_hp') or row.get('phone_number') or '').strip()[:20]
        jk = (row.get('bio_jk') or '').strip().upper()[:1]
        if jk not in ('L', 'P'):
            jk = 'L'

        # User baru — password: NIK (kalau valid) / fallback_password / unusable
        user = User.objects.create_user(
            username=email, email=email, first_name=nama[:30],
        )
        if self.use_unusable_password:
            user.set_unusable_password()
        elif nik:
            user.set_password(nik)
        else:
            user.set_password(self.fallback_password)
        user.save(update_fields=['password'])

        pendaftar = Pendaftar.objects.create(
            user=user,
            no_daftar=generate_no_daftar(jalur),
            nama=nama[:200],
            NIK=nik,
            jenis_kelamin=jk,
            tempat_lahir=(row.get('bio_tempat_lahir') or '').strip()[:100],
            tanggal_lahir=row.get('bio_tgl_lahir') or None,
            agama='Islam',
            no_hp=no_hp,
            jalur=jalur,
            prodi1=prodi1,
            prodi2=prodi2,
        )
        if row.get('register_at'):
            # created_at punya auto_now_add — override langsung via UPDATE
            Pendaftar.objects.filter(pk=pendaftar.pk).update(
                created_at=row['register_at']
            )

        # Sekolah (kalau ada asal_sekolah)
        if (row.get('bio_sekolah') or '').strip():
            reg_year = (row.get('register_at').year if row.get('register_at') else date.today().year)
            Sekolah.objects.create(
                pendaftar=pendaftar,
                nama=row['bio_sekolah'].strip()[:200],
                jurusan='',
                nisn=(row.get('bio_nisn') or '')[:20],
                akreditasi='Belum',
                tahun_lulus=reg_year - 1,
            )

        # Ortu (kalau ada nama_orangtua)
        if (row.get('bio_ortu_nama') or '').strip():
            hubungan_raw = (row.get('bio_ortu_hubungan') or '').strip().lower()
            hubungan = HUBUNGAN_MAP.get(hubungan_raw, 'ayah')
            penghasilan_raw = row.get('bio_ortu_penghasilan')
            try:
                penghasilan = int(penghasilan_raw) if penghasilan_raw not in (None, '') else None
            except (ValueError, TypeError):
                penghasilan = None
            Ortu.objects.create(
                pendaftar=pendaftar,
                nama=row['bio_ortu_nama'].strip()[:200],
                hubungan=hubungan,
                pekerjaan=(row.get('bio_ortu_pekerjaan') or '').strip()[:100],
                pendidikan='',
                penghasilan=penghasilan,
                no_hp=(row.get('bio_ortu_hp') or '')[:20],
            )

        # Jalur-specific record (RaportBerkas/BeasiswaDaftar/UmumDaftar) + Registrasi.
        #
        # Laravel status:
        #   0 Berkas Belum Lengkap   |  3 Diterima #2 (lulus)
        #   1 Slip Dikonfirmasi       |  4 Ditolak
        #   2 Diterima #1 (lulus)     |  5 Lolos Beasiswa Parsial (lulus)
        lstatus = row.get('status') or 0
        is_lulus = lstatus in (2, 3, 5)

        # Prodi yang diterima: status=2→prodi1, status=3→prodi2, status=5→prodi1
        prodi_lulus = prodi1 if lstatus in (2, 5) else (prodi2 if lstatus == 3 else None)

        self._create_jalur_record(pendaftar, jalur, lstatus, prodi_lulus, row)

        if is_lulus:
            Registrasi.objects.create(
                pendaftar=pendaftar,
                status=0,  # Belum daftar ulang di sistem baru
                kelas=kelas,
            )

    def _create_jalur_record(self, pendaftar, jalur, lstatus, prodi_lulus, row):
        """Buat record jalur-specific sesuai jalur.

        Mapping Laravel status → status jalur Django:
          0,1  → belum lengkap / menunggu validasi
          2,3,5 → lulus
          4    → tidak lulus
        """
        ket = (row.get('referensi') or '').strip()[:500]
        tgl_proses = row.get('register_at')

        if jalur == 'raport':
            st_map = {0: 0, 1: 1, 2: 3, 3: 3, 4: 4, 5: 3}
            RaportBerkas.objects.create(
                pendaftar=pendaftar,
                status=st_map.get(lstatus, 0),
                prodi_lulus=prodi_lulus,
                keterangan=ket,
                tgl_diproses=tgl_proses if lstatus in (2, 3, 4, 5) else None,
                diproses_oleh='Migrasi pmb.bim.ac.id' if lstatus in (2, 3, 4, 5) else '',
            )
        elif jalur == 'beasiswa':
            st_map = {0: 0, 1: 1, 2: 3, 3: 3, 4: 4, 5: 3}
            BeasiswaDaftar.objects.create(
                pendaftar=pendaftar,
                jenis_beasiswa='BTUMD',  # default; admin bisa ubah via panel
                status_seleksi=st_map.get(lstatus, 0),
                prodi_lulus=prodi_lulus,
                catatan_panitia=ket,
                tgl_diproses=tgl_proses if lstatus in (2, 3, 4, 5) else None,
                diproses_oleh='Migrasi pmb.bim.ac.id' if lstatus in (2, 3, 4, 5) else '',
            )
        elif jalur == 'umum':
            st_map = {0: 0, 1: 1, 2: 2, 3: 2, 4: 3, 5: 2}
            UmumDaftar.objects.create(
                pendaftar=pendaftar,
                status=st_map.get(lstatus, 0),
                prodi_lulus=prodi_lulus,
                keterangan=ket,
                tgl_diproses=tgl_proses if lstatus in (2, 3, 4, 5) else None,
                diproses_oleh='Migrasi pmb.bim.ac.id' if lstatus in (2, 3, 4, 5) else '',
            )

    def _find_prodi(self, laravel_id, laravel_name):
        """Match prodi: 1) by id (Laravel pk = Django pk), 2) fallback by nama
        (case-insensitive, dengan alias untuk handle beda ejaan)."""
        if laravel_id is None:
            return None

        # Primary: by id
        p = self.prodi_by_id.get(laravel_id)
        if p:
            return p

        # Fallback: by nama (dengan alias)
        name = (laravel_name or '').strip().lower()
        name = PRODI_NAME_ALIAS.get(name, name)
        return self.prodi_by_name.get(name)

    # ── Logging helpers ──────────────────────────────────────────────────────

    def _ok(self, msg):
        self.stdout.write(self.style.SUCCESS(msg))

    def _info(self, msg):
        self.stdout.write(msg)

    def _warn(self, msg):
        self.stdout.write(self.style.WARNING(msg))

    def _err(self, msg):
        self.stdout.write(self.style.ERROR(msg))
