"""Import pendaftar dari CSV (mis. export Google Form PMB) ke pmb_terpadu.

Logika import ada di pendaftaran/importer.py (dipakai bersama menu panel).

Usage:
  py manage.py import_pendaftar_csv --template pendaftar_template.csv  # buat contoh CSV
  py manage.py import_pendaftar_csv data.csv --dry-run                 # preview
  py manage.py import_pendaftar_csv data.csv                           # import
  py manage.py import_pendaftar_csv data.csv --allow-empty-nik
  py manage.py import_pendaftar_csv data.csv --force-refresh           # re-import (hapus lama)
"""
from django.core.management.base import BaseCommand, CommandError

from pendaftaran.importer import (
    Importer, read_rows, build_template_csv, JALUR_VALID,
)


class Command(BaseCommand):
    help = 'Import pendaftar dari CSV (Google Form PMB) ke pmb_terpadu.'

    def add_arguments(self, parser):
        parser.add_argument('csv_path', nargs='?', help='Path file CSV')
        parser.add_argument('--template', metavar='PATH',
                            help='Tulis contoh CSV (header + 1 baris) ke PATH lalu keluar.')
        parser.add_argument('--dry-run', action='store_true', help='Preview tanpa menulis ke DB.')
        parser.add_argument('--limit', type=int, default=0, help='Batasi jumlah baris (0 = semua).')
        parser.add_argument('--default-jalur', default='raport', choices=sorted(JALUR_VALID))
        parser.add_argument('--default-agama', default='Islam')
        parser.add_argument('--allow-empty-nik', action='store_true',
                            help='Izinkan NIK kosong (default: baris di-skip).')
        parser.add_argument('--fallback-password', default='pmb2026')
        parser.add_argument('--force-refresh', action='store_true',
                            help='Hapus pendaftar existing (email/NIK) lalu import ulang.')

    def handle(self, *args, **opts):
        if opts.get('template'):
            with open(opts['template'], 'w', newline='', encoding='utf-8-sig') as f:
                f.write(build_template_csv())
            self.stdout.write(self.style.SUCCESS(f'Template CSV ditulis: {opts["template"]}'))
            self.stdout.write('Kolom wajib: email, nama, nik (16 digit), prodi1.')
            return

        csv_path = opts.get('csv_path')
        if not csv_path:
            raise CommandError('Sebutkan path CSV, atau pakai --template untuk membuat contoh.')

        try:
            with open(csv_path, newline='', encoding='utf-8-sig') as f:
                rows = read_rows(f)
        except OSError as e:
            raise CommandError(f'Tidak bisa membuka CSV: {e}')
        except ValueError as e:
            raise CommandError(str(e))

        if opts['limit']:
            rows = rows[:opts['limit']]

        self.stdout.write(self.style.NOTICE(f'Membaca {len(rows)} baris dari {csv_path}'))
        if opts['dry_run']:
            self.stdout.write(self.style.WARNING('[DRY RUN] tidak menulis ke DB.'))

        try:
            importer = Importer(
                dry_run=opts['dry_run'],
                default_jalur=opts['default_jalur'],
                default_agama=opts['default_agama'],
                allow_empty_nik=opts['allow_empty_nik'],
                fallback_password=opts['fallback_password'],
                force_refresh=opts['force_refresh'],
            )
        except ValueError as e:
            raise CommandError(str(e))

        rep = importer.run(rows)

        for r in rep.rows:
            line = f'[{r.index}/{rep.total} {r.label}] {r.status.upper()}'
            if r.message:
                line += f': {r.message}'
            if r.warnings:
                line += '  ⚠ ' + '; '.join(r.warnings)
            style = {'imported': self.style.SUCCESS, 'error': self.style.ERROR,
                     'skipped_invalid': self.style.WARNING,
                     'skipped_existing': self.style.NOTICE}.get(r.status, self.style.NOTICE)
            self.stdout.write(style(line))

        self.stdout.write(self.style.SUCCESS(
            f'\nSelesai. imported={rep.imported} skipped_existing={rep.skipped_existing} '
            f'skipped_invalid={rep.skipped_invalid} errored={rep.errored}'
        ))
