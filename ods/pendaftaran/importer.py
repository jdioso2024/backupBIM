"""Logika import pendaftar dari CSV — dipakai bersama oleh management command
(`import_pendaftar_csv`) dan menu panel (upload CSV).

Public API:
    TEMPLATE_HEADER, TEMPLATE_EXAMPLE
    build_template_csv() -> str
    read_rows(file_obj) -> list[dict]        # baca + normalisasi header
    Importer(...).run(rows) -> ImportReport
    import_csv_file(file_obj, **opts) -> ImportReport
"""
import csv
import io
import re
from dataclasses import dataclass, field
from datetime import date, datetime

from django.db import transaction
from django.contrib.auth.models import User

from core.models import Prodi, Kota, Provinsi
from .models import Pendaftar, Alamat, Sekolah, Ortu, generate_no_daftar
from raport.models import RaportBerkas
from beasiswa.models import BeasiswaDaftar
from umum.models import UmumDaftar


TEMPLATE_HEADER = [
    'email', 'nama', 'nik', 'no_kk', 'jenis_kelamin', 'agama',
    'ttl', 'tempat_lahir', 'tanggal_lahir', 'no_hp', 'jalur',
    'prodi1', 'prodi2', 'password',
    'asal_sekolah', 'jurusan', 'nisn', 'akreditasi', 'tahun_lulus',
    'alamat', 'rt', 'rw', 'kelurahan', 'kota', 'provinsi',
    'ayah_nama', 'ayah_pekerjaan', 'ayah_hp', 'ayah_pendidikan', 'ayah_penghasilan',
    'ibu_nama', 'ibu_pekerjaan', 'ibu_hp',
    'sumber_info', 'sumber_info_nama', 'status', 'catatan', 'timestamp',
    'link_kk', 'link_ktp', 'link_ijazah', 'link_sertifikat', 'link_ktp_ibu',
]

TEMPLATE_EXAMPLE = {
    'email': 'budi@example.com', 'nama': 'Budi Santoso', 'nik': '6371010101050001',
    'no_kk': '', 'jenis_kelamin': 'L', 'agama': 'Islam',
    'ttl': 'Banjarmasin, 13 Oktober 2006', 'tempat_lahir': '', 'tanggal_lahir': '',
    'no_hp': '081234567890', 'jalur': 'raport',
    'prodi1': 'Bisnis Digital', 'prodi2': 'Kewirausahaan', 'password': '',
    'asal_sekolah': 'SMAN 1 Paringin', 'jurusan': 'IPA', 'nisn': '0012345678',
    'akreditasi': 'A', 'tahun_lulus': '2025',
    'alamat': 'Jl. Merdeka No. 1', 'rt': '01', 'rw': '02', 'kelurahan': 'Sidakarya',
    'kota': 'Kota Denpasar', 'provinsi': 'Bali',
    'ayah_nama': 'Slamet', 'ayah_pekerjaan': 'Petani', 'ayah_hp': '081200000000',
    'ayah_pendidikan': 'SMA/SMK', 'ayah_penghasilan': '2000000',
    'ibu_nama': 'Siti', 'ibu_pekerjaan': 'Ibu Rumah Tangga', 'ibu_hp': '',
    'sumber_info': 'sekolah', 'sumber_info_nama': '', 'status': '', 'catatan': '',
    'timestamp': '12/6/2025 9:33:47',
    'link_kk': '', 'link_ktp': '', 'link_ijazah': '', 'link_sertifikat': '', 'link_ktp_ibu': '',
}

REQUIRED_COLS = ('email', 'nama', 'prodi1')  # nik wajib kecuali allow_empty_nik

BULAN = {
    'januari': 1, 'jan': 1,
    'februari': 2, 'feb': 2, 'pebruari': 2, 'feber': 2,
    'maret': 3, 'mar': 3,
    'april': 4, 'apr': 4,
    'mei': 5,
    'juni': 6, 'jun': 6,
    'juli': 7, 'jul': 7,
    'agustus': 8, 'agu': 8, 'agt': 8, 'ags': 8, 'agus': 8,
    'september': 9, 'sep': 9, 'sept': 9,
    'oktober': 10, 'okt': 10, 'oct': 10,
    'november': 11, 'nov': 11, 'nopember': 11, 'nop': 11,
    'desember': 12, 'des': 12, 'dec': 12,
}

HUBUNGAN_AYAH = 'ayah'
HUBUNGAN_IBU = 'ibu'
AGAMA_VALID = {'Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'}
AKRED_VALID = {'A', 'B', 'C', 'Belum'}
JALUR_VALID = {'raport', 'beasiswa', 'umum'}
STATUS_UNDUR = {'batal', 'undur', 'undur diri', 'mengundurkan diri', 'mengundurkan'}


# ── transformasi pure ─────────────────────────────────────────────────────────

def clean_nik(s):
    return re.sub(r'\D', '', str(s or ''))[:16]


def norm_hp(s):
    s = re.sub(r'[^\d+]', '', str(s or ''))
    if s.startswith('+62'):
        s = '0' + s[3:]
    elif s.startswith('62') and not s.startswith('0'):
        s = '0' + s[2:]
    return s[:20]


def _safe_date(y, mo, d):
    try:
        return date(y, mo, d)
    except ValueError:
        return None


def parse_tanggal(s):
    """Parse berbagai format tanggal Indonesia → date | None."""
    if not s:
        return None
    s = str(s).strip()
    if not s:
        return None
    m = re.match(r'^(\d{4})-(\d{1,2})-(\d{1,2})$', s)
    if m:
        y, mo, d = (int(x) for x in m.groups())
        return _safe_date(y, mo, d)
    low = s.lower()
    m = re.search(r'(\d{1,2})\s*[-\s]?\s*([a-z]+)\s*[-\s]?\s*(\d{4})', low)
    if m and m.group(2) in BULAN:
        return _safe_date(int(m.group(3)), BULAN[m.group(2)], int(m.group(1)))
    m = re.search(r'(\d{1,2})\s*[-/.,]\s*(\d{1,2})\s*[-/.,]\s*(\d{4})', s)
    if m:
        return _safe_date(int(m.group(3)), int(m.group(2)), int(m.group(1)))
    m = re.search(r'(\d{4})\s*[-/.,]\s*(\d{1,2})\s*[-/.,]\s*(\d{1,2})', s)
    if m:
        return _safe_date(int(m.group(1)), int(m.group(2)), int(m.group(3)))
    return None


def split_ttl(ttl):
    """Pisah 'Tempat, Tanggal Lahir' → (tempat, date|None)."""
    ttl = (ttl or '').strip()
    if not ttl:
        return '', None
    if ',' in ttl:
        tempat, sisa = ttl.split(',', 1)
        return tempat.strip(), parse_tanggal(sisa.strip())
    m = re.search(r'\d', ttl)
    if m:
        return ttl[:m.start()].strip(), parse_tanggal(ttl[m.start():].strip())
    return ttl, None


def parse_timestamp(s):
    s = (s or '').strip()
    if not s:
        return None
    for fmt in ('%m/%d/%Y %H:%M:%S', '%d/%m/%Y %H:%M:%S', '%Y-%m-%d %H:%M:%S',
                '%m/%d/%Y', '%d/%m/%Y', '%Y-%m-%d'):
        try:
            return datetime.strptime(s, fmt)
        except ValueError:
            continue
    return None


# ── CSV helpers ───────────────────────────────────────────────────────────────

def build_template_csv():
    """Return string CSV: header + 1 baris contoh."""
    buf = io.StringIO()
    w = csv.DictWriter(buf, fieldnames=TEMPLATE_HEADER)
    w.writeheader()
    w.writerow(TEMPLATE_EXAMPLE)
    return buf.getvalue()


def read_rows(file_obj):
    """Baca file CSV (text mode) → list[dict] dengan header dinormalisasi
    (lower, trim, spasi→underscore). Baris kosong dibuang."""
    reader = csv.DictReader(file_obj)
    if not reader.fieldnames:
        raise ValueError('CSV kosong / tidak ada header.')
    norm = {fn: fn.strip().lower().replace(' ', '_') for fn in reader.fieldnames}
    rows = []
    for raw in reader:
        row = {norm.get(k, k): (v or '').strip() for k, v in raw.items()}
        if any(row.values()):
            rows.append(row)
    return rows


# ── Report ────────────────────────────────────────────────────────────────────

@dataclass
class RowResult:
    index: int
    label: str
    status: str            # imported | skipped_existing | skipped_invalid | error
    message: str = ''
    warnings: list = field(default_factory=list)


@dataclass
class ImportReport:
    dry_run: bool = False
    imported: int = 0
    skipped_existing: int = 0
    skipped_invalid: int = 0
    errored: int = 0
    total: int = 0
    rows: list = field(default_factory=list)


# ── Importer ──────────────────────────────────────────────────────────────────

class Importer:
    def __init__(self, *, dry_run=False, default_jalur='raport', default_agama='Islam',
                 allow_empty_nik=False, fallback_password='pmb2026', force_refresh=False):
        self.dry_run = dry_run
        self.default_jalur = default_jalur if default_jalur in JALUR_VALID else 'raport'
        self.default_agama = default_agama or 'Islam'
        self.allow_empty_nik = allow_empty_nik
        self.fallback_password = fallback_password
        self.force_refresh = force_refresh

        all_prodi = list(Prodi.objects.all())
        if not all_prodi:
            raise ValueError('Tabel Prodi kosong. Seed Prodi dulu sebelum import.')
        self.prodi_by_name = {p.nama.strip().lower(): p for p in all_prodi}
        self.prodi_by_kode = {p.kode.strip().lower(): p for p in all_prodi if p.kode}
        self.kota_by_name = {k.nama.strip().lower(): k for k in Kota.objects.all()}
        self.prov_by_name = {p.nama.strip().lower(): p for p in Provinsi.objects.all()}

    def run(self, rows):
        rep = ImportReport(dry_run=self.dry_run, total=len(rows))
        for i, row in enumerate(rows, start=1):
            res = self._process(i, row)
            rep.rows.append(res)
            setattr(rep, {'imported': 'imported', 'skipped_existing': 'skipped_existing',
                          'skipped_invalid': 'skipped_invalid', 'error': 'errored'}[res.status],
                    getattr(rep, {'imported': 'imported', 'skipped_existing': 'skipped_existing',
                                  'skipped_invalid': 'skipped_invalid', 'error': 'errored'}[res.status]) + 1)
        return rep

    def _process(self, i, row):
        email = (row.get('email') or '').strip().lower()
        nama = (row.get('nama') or '').strip()
        label = email or nama or f'baris {i}'

        if not email:
            return RowResult(i, label, 'skipped_invalid', 'email kosong')
        if not nama:
            return RowResult(i, label, 'skipped_invalid', 'nama kosong')

        nik = clean_nik(row.get('nik'))
        if len(nik) != 16:
            if not self.allow_empty_nik:
                return RowResult(i, label, 'skipped_invalid',
                                 f'NIK tidak 16 digit ({row.get("nik")!r})')
            nik = ''

        existing_user = User.objects.filter(username=email).first()
        existing_pend = Pendaftar.objects.filter(NIK=nik).first() if nik else None
        if existing_user or existing_pend:
            if not self.force_refresh:
                return RowResult(i, label, 'skipped_existing', 'email/NIK sudah ada')
            if not self.dry_run:
                if existing_pend:
                    existing_pend.user.delete()
                elif existing_user:
                    existing_user.delete()

        warns = []
        try:
            if self.dry_run:
                self._resolve_common(row, warns)   # validasi tanpa tulis
            else:
                with transaction.atomic():
                    self._create(row, email, nama, nik, warns)
            return RowResult(i, label, 'imported', '', warns)
        except Exception as e:
            return RowResult(i, label, 'error', str(e), warns)

    def _resolve_common(self, row, warns):
        jalur = (row.get('jalur') or '').strip().lower() or self.default_jalur
        if jalur not in JALUR_VALID:
            jalur = self.default_jalur

        tempat = (row.get('tempat_lahir') or '').strip()
        tgl = parse_tanggal(row.get('tanggal_lahir'))
        if (not tempat or not tgl) and (row.get('ttl') or '').strip():
            t2, d2 = split_ttl(row.get('ttl'))
            tempat = tempat or t2
            tgl = tgl or d2
        if (row.get('tanggal_lahir') or row.get('ttl')) and not tgl:
            warns.append('tanggal_lahir tidak terparse')

        jk = (row.get('jenis_kelamin') or '').strip().upper()[:1]
        if jk not in ('L', 'P'):
            jk = 'L'
            warns.append('jenis_kelamin default L (verifikasi)')

        agama = (row.get('agama') or '').strip().title()
        if agama not in AGAMA_VALID:
            if not (row.get('agama') or '').strip():
                warns.append(f'agama default {self.default_agama} (verifikasi)')
            agama = self.default_agama

        prodi1 = self._find_prodi(row.get('prodi1'))
        if not prodi1:
            raise ValueError(f'prodi1 tidak ditemukan: {row.get("prodi1")!r}')
        prodi2 = self._find_prodi(row.get('prodi2'))
        if prodi2 and prodi2.pk == prodi1.pk:
            prodi2 = None

        return jalur, tempat, tgl, jk, agama, prodi1, prodi2

    def _create(self, row, email, nama, nik, warns):
        jalur, tempat, tgl, jk, agama, prodi1, prodi2 = self._resolve_common(row, warns)

        password = (row.get('password') or '').strip() or nik or self.fallback_password
        user = User.objects.create_user(
            username=email, email=email, first_name=nama[:30], password=password,
        )

        pendaftar = Pendaftar.objects.create(
            user=user,
            no_daftar=generate_no_daftar(jalur),
            nama=nama[:200],
            NIK=nik,
            no_kk=clean_nik(row.get('no_kk')),
            jenis_kelamin=jk,
            tempat_lahir=tempat[:100],
            tanggal_lahir=tgl,
            agama=agama,
            no_hp=norm_hp(row.get('no_hp')),
            jalur=jalur,
            prodi1=prodi1,
            prodi2=prodi2,
            sumber_info=(row.get('sumber_info') or '').strip().lower()[:20],
            sumber_info_nama=(row.get('sumber_info_nama') or '').strip()[:100],
        )

        ts = parse_timestamp(row.get('timestamp'))
        if ts:
            Pendaftar.objects.filter(pk=pendaftar.pk).update(created_at=ts)

        asal = (row.get('asal_sekolah') or '').strip()
        if asal:
            akred = (row.get('akreditasi') or '').strip().title()
            akred = akred if akred in AKRED_VALID else 'Belum'
            try:
                tahun_lulus = int(re.sub(r'\D', '', row.get('tahun_lulus') or '') or 0)
            except ValueError:
                tahun_lulus = 0
            if not tahun_lulus:
                tahun_lulus = (tgl.year + 18) if tgl else date.today().year
            Sekolah.objects.create(
                pendaftar=pendaftar,
                nama=asal[:200],
                jurusan=(row.get('jurusan') or '').strip()[:100],
                nisn=re.sub(r'\s', '', row.get('nisn') or '')[:20],
                akreditasi=akred,
                tahun_lulus=tahun_lulus,
            )

        jalan = (row.get('alamat') or '').strip()
        kelurahan = (row.get('kelurahan') or '').strip()
        if jalan or kelurahan:
            Alamat.objects.create(
                pendaftar=pendaftar,
                jalan=jalan,
                rt=(row.get('rt') or '').strip()[:5],
                rw=(row.get('rw') or '').strip()[:5],
                kelurahan=kelurahan[:100],
                kota=self.kota_by_name.get((row.get('kota') or '').strip().lower()),
                provinsi=self.prov_by_name.get((row.get('provinsi') or '').strip().lower()),
            )

        if (row.get('ayah_nama') or '').strip():
            self._create_ortu(pendaftar, HUBUNGAN_AYAH, row.get('ayah_nama'),
                              row.get('ayah_pekerjaan'), row.get('ayah_hp'),
                              row.get('ayah_pendidikan'), row.get('ayah_penghasilan'))
        if (row.get('ibu_nama') or '').strip():
            self._create_ortu(pendaftar, HUBUNGAN_IBU, row.get('ibu_nama'),
                              row.get('ibu_pekerjaan'), row.get('ibu_hp'), '', '')

        self._create_jalur(pendaftar, jalur, row)

    def _create_ortu(self, pendaftar, hubungan, nama, pekerjaan, hp, pendidikan, penghasilan):
        try:
            peng = int(re.sub(r'\D', '', str(penghasilan or '')) or 0) or None
        except ValueError:
            peng = None
        Ortu.objects.create(
            pendaftar=pendaftar,
            nama=(nama or '').strip()[:200],
            hubungan=hubungan,
            pekerjaan=(pekerjaan or '').strip()[:100],
            pendidikan=(pendidikan or '').strip()[:10],
            penghasilan=peng,
            no_hp=norm_hp(hp),
        )

    def _create_jalur(self, pendaftar, jalur, row):
        links = []
        for label, key in (('KK', 'link_kk'), ('KTP', 'link_ktp'),
                           ('Ijazah/SKL', 'link_ijazah'), ('Sertifikat', 'link_sertifikat'),
                           ('KTP Ibu', 'link_ktp_ibu')):
            v = (row.get(key) or '').strip()
            if v:
                links.append(f'{label}: {v}')
        catatan = (row.get('catatan') or '').strip()
        status_raw = (row.get('status') or '').strip().lower()
        is_undur = status_raw in STATUS_UNDUR

        parts = []
        if catatan:
            parts.append(catatan)
        if links:
            parts.append('Dokumen (upload manual): ' + ' | '.join(links))
        if is_undur:
            parts.append(f'STATUS: {status_raw}')
        ket = '\n'.join(parts)[:2000]

        if jalur == 'raport':
            RaportBerkas.objects.create(
                pendaftar=pendaftar,
                status=6 if is_undur else 0,
                keterangan=ket,
                diproses_oleh='Import CSV' if is_undur else '',
            )
        elif jalur == 'beasiswa':
            BeasiswaDaftar.objects.create(
                pendaftar=pendaftar, jenis_beasiswa='BTUMD',
                status_seleksi=0, keterangan=ket,
            )
        elif jalur == 'umum':
            UmumDaftar.objects.create(pendaftar=pendaftar, status=0, keterangan=ket)

    def _find_prodi(self, value):
        v = (value or '').strip().lower()
        if not v:
            return None
        return self.prodi_by_name.get(v) or self.prodi_by_kode.get(v)


def import_csv_file(file_obj, **opts):
    """Baca file CSV (text mode) lalu import. Return ImportReport."""
    rows = read_rows(file_obj)
    return Importer(**opts).run(rows)
