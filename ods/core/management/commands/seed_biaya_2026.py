"""Seed data biaya kuliah PMB 2026 dari tabel resmi.

Usage:
  py manage.py seed_biaya_2026
  py manage.py seed_biaya_2026 --tahun 2026 --reset-keterangan
  py manage.py seed_biaya_2026 --dry-run

Idempotent — pakai update_or_create, rerun aman. Match Prodi by nama
(case-insensitive) dengan alias untuk handle beda ejaan.
"""
from decimal import Decimal

from django.core.management.base import BaseCommand
from django.db import transaction

from core.models import (
    BiayaKuliahPeriode, BiayaKuliahProdi, KETERANGAN_DEFAULT, Prodi,
)


# ── Data tabel resmi 2026 ─────────────────────────────────────────────────────

BIAYA_PENDAFTARAN = 250_000
BIAYA_PKKMB = 0  # tidak ada PKKMB di tabel 2026 baru


REGULER = [
    {'prodi': 'Bisnis Digital',    'dpp1': 4_000_000, 'dpp2': 4_000_000, 'spp': 6_000_000, 'reg': 7_000_000},
    {'prodi': 'Kewirausahaan',     'dpp1': 3_750_000, 'dpp2': 3_750_000, 'spp': 5_100_000, 'reg': 6_300_000},
    {'prodi': 'Teknologi Pangan',  'dpp1': 3_500_000, 'dpp2': 3_500_000, 'spp': 4_200_000, 'reg': 5_600_000},
    {'prodi': 'Hukum',             'dpp1': 3_500_000, 'dpp2': 3_500_000, 'spp': 4_400_000, 'reg': 5_700_000},
    {'prodi': 'Informatika',       'dpp1': 3_500_000, 'dpp2': 3_500_000, 'spp': 4_800_000, 'reg': 5_900_000},
    {'prodi': 'Manajemen',         'dpp1': 3_500_000, 'dpp2': 3_500_000, 'spp': 4_400_000, 'reg': 5_700_000},
    {'prodi': 'Sistem Informasi',  'dpp1': 3_500_000, 'dpp2': 3_500_000, 'spp': 4_800_000, 'reg': 5_900_000},
]

KARYAWAN = [
    {'prodi': 'Bisnis Digital',    'dpp1': 4_800_000, 'dpp2': 4_800_000, 'spp': 7_200_000, 'reg': 8_400_000},
    {'prodi': 'Kewirausahaan',     'dpp1': 4_500_000, 'dpp2': 4_500_000, 'spp': 6_120_000, 'reg': 7_560_000},
    {'prodi': 'Teknologi Pangan',  'dpp1': 4_200_000, 'dpp2': 4_200_000, 'spp': 5_040_000, 'reg': 6_720_000},
    {'prodi': 'Hukum',             'dpp1': 4_200_000, 'dpp2': 4_200_000, 'spp': 5_280_000, 'reg': 6_840_000},
    {'prodi': 'Informatika',       'dpp1': 4_200_000, 'dpp2': 4_200_000, 'spp': 5_760_000, 'reg': 7_080_000},
    {'prodi': 'Manajemen',         'dpp1': 4_200_000, 'dpp2': 4_200_000, 'spp': 5_280_000, 'reg': 6_840_000},
    {'prodi': 'Sistem Informasi',  'dpp1': 4_200_000, 'dpp2': 4_200_000, 'spp': 5_760_000, 'reg': 7_080_000},
]

INTERNASIONAL = [
    {'prodi': 'Bisnis Digital',    'dpp1': 5_200_000, 'dpp2': 5_200_000, 'spp': 7_800_000, 'reg': 9_100_000},
    {'prodi': 'Kewirausahaan',     'dpp1': 4_875_000, 'dpp2': 4_875_000, 'spp': 6_630_000, 'reg': 8_190_000},
    {'prodi': 'Sistem Informasi',  'dpp1': 4_550_000, 'dpp2': 4_550_000, 'spp': 6_240_000, 'reg': 7_670_000},
]

BOARDING = [
    {'prodi': 'Bisnis Digital',    'pengembangan': 1_200_000, 'hidup': 7_500_000, 'dpp_spp': 10_600_000},
    {'prodi': 'Kewirausahaan',     'pengembangan': 1_200_000, 'hidup': 7_500_000, 'dpp_spp':  9_600_000},
    {'prodi': 'Sistem Informasi',  'pengembangan': 1_200_000, 'hidup': 7_500_000, 'dpp_spp':  9_600_000},
]

# Alias nama prodi: tabel resmi → Django Prodi.nama (kalau beda ejaan).
PRODI_ALIASES = {
    'informatika': 'teknologi informatika',
}


class Command(BaseCommand):
    help = 'Seed biaya kuliah PMB 2026 (Reguler/Karyawan/Internasional/Boarding) + keterangan.'

    def add_arguments(self, parser):
        parser.add_argument('--tahun', default='2026',
                            help='Tahun PMB (default: 2026)')
        parser.add_argument('--dry-run', action='store_true',
                            help='Preview tanpa menulis ke DB.')
        parser.add_argument('--reset-keterangan', action='store_true',
                            help='Paksa update keterangan periode ke KETERANGAN_DEFAULT '
                                 '(default hanya set saat create periode baru).')

    def handle(self, *args, **opts):
        self.dry_run = opts['dry_run']
        tahun = opts['tahun']
        reset_keterangan = opts['reset_keterangan']

        if self.dry_run:
            self.stdout.write(self.style.WARNING('[DRY RUN] tidak menulis ke DB.'))

        # Bangun cache prodi
        prodi_by_name = {p.nama.strip().lower(): p for p in Prodi.objects.all()}
        if not prodi_by_name:
            self.stdout.write(self.style.ERROR('Tabel Prodi kosong — seed Prodi dulu.'))
            return

        if not self.dry_run:
            self._seed(tahun, prodi_by_name, reset_keterangan)
        else:
            self._preview(tahun, prodi_by_name)

    @transaction.atomic
    def _seed(self, tahun, prodi_by_name, reset_keterangan):
        # 1. Periode header
        periode, created = BiayaKuliahPeriode.objects.update_or_create(
            tahun_pmb=tahun,
            defaults={
                'biaya_pendaftaran': BIAYA_PENDAFTARAN,
                'biaya_pkkmb':       BIAYA_PKKMB,
                'aktif':             True,
            },
        )
        if created or reset_keterangan:
            periode.keterangan = KETERANGAN_DEFAULT
            periode.save(update_fields=['keterangan'])
            self.stdout.write(self.style.SUCCESS(
                f'Periode {tahun}: keterangan {"di-init" if created else "di-reset"} ke default baru.'
            ))

        action = 'CREATED' if created else 'UPDATED'
        self.stdout.write(self.style.SUCCESS(
            f'{action} BiayaKuliahPeriode {tahun} '
            f'(pendaftaran={BIAYA_PENDAFTARAN}, pkkmb={BIAYA_PKKMB}).'
        ))

        # 2. Detail per kelas
        total_seeded = 0
        for kelas, rows in [
            ('reguler',       REGULER),
            ('karyawan',      KARYAWAN),
            ('internasional', INTERNASIONAL),
        ]:
            for row in rows:
                prodi = self._find_prodi(row['prodi'], prodi_by_name)
                if not prodi:
                    self.stdout.write(self.style.WARNING(
                        f'  SKIP {kelas} {row["prodi"]!r}: prodi tidak ditemukan di Django.'
                    ))
                    continue

                BiayaKuliahProdi.objects.update_or_create(
                    periode=periode, prodi=prodi, jenis_kelas=kelas,
                    defaults={
                        'dpp_cicilan_1':         row['dpp1'],
                        'dpp_cicilan_2':         row['dpp2'],
                        'spp_per_semester':      row['spp'],
                        'biaya_saat_registrasi': row['reg'],
                        # field boarding di-zero supaya tidak nyangkut value lama
                        'pengembangan':  Decimal('0'),
                        'biaya_hidup':   Decimal('0'),
                        'dpp_spp_total': Decimal('0'),
                    },
                )
                total_seeded += 1

        # 3. International Boarding
        for row in BOARDING:
            prodi = self._find_prodi(row['prodi'], prodi_by_name)
            if not prodi:
                self.stdout.write(self.style.WARNING(
                    f'  SKIP boarding {row["prodi"]!r}: prodi tidak ditemukan.'
                ))
                continue

            BiayaKuliahProdi.objects.update_or_create(
                periode=periode, prodi=prodi, jenis_kelas='boarding',
                defaults={
                    'dpp_cicilan_1':         Decimal('0'),
                    'dpp_cicilan_2':         Decimal('0'),
                    'spp_per_semester':      Decimal('0'),
                    'biaya_saat_registrasi': Decimal('0'),
                    'pengembangan':  row['pengembangan'],
                    'biaya_hidup':   row['hidup'],
                    'dpp_spp_total': row['dpp_spp'],
                },
            )
            total_seeded += 1

        self.stdout.write(self.style.SUCCESS(
            f'\nSelesai. Seeded {total_seeded} rincian biaya × kelas untuk periode {tahun}.'
        ))

    def _preview(self, tahun, prodi_by_name):
        self.stdout.write(f'Akan seed periode {tahun}:')
        self.stdout.write(f'  biaya_pendaftaran = Rp {BIAYA_PENDAFTARAN:,}')
        self.stdout.write(f'  biaya_pkkmb       = Rp {BIAYA_PKKMB:,}')

        for kelas, rows in [
            ('reguler',       REGULER),
            ('karyawan',      KARYAWAN),
            ('internasional', INTERNASIONAL),
            ('boarding',      BOARDING),
        ]:
            self.stdout.write(f'\n[{kelas}] {len(rows)} prodi:')
            for row in rows:
                prodi = self._find_prodi(row['prodi'], prodi_by_name)
                mark = '✓' if prodi else '✗ NOT FOUND'
                self.stdout.write(f'  {mark} {row["prodi"]}')

    def _find_prodi(self, name, cache):
        key = name.strip().lower()
        key = PRODI_ALIASES.get(key, key)
        return cache.get(key)
