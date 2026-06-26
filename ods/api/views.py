"""
Read-only JSON API consumed by the PMB Laravel dashboard (Pimpinan > Monitor).

Authentication: every request must carry the header
    X-PMB-Token: <value of PMB_API_TOKEN env var on the ODS side>

PMB sends the same token via its ODS_API_TOKEN env var.

Status-code semantics (verified against model choices):
  umum_daftar.status      2  = Lulus (diterima)
  raport_berkas.status    3  = Lulus (diterima)   6 = Undur Diri
  beasiswa_daftar.status_seleksi  3 = Lolos (diterima)
  registrasi.status       1  = Sudah Daftar Ulang  2 = Batal/Undur Diri

Jalur label mapping:
  raport   → Prestasi
  umum     → Reguler
  beasiswa → KIP (if jenis_beasiswa=='KIP') else Beasiswa
"""

import os
from functools import wraps

from django.http import JsonResponse
from django.db.models import Count
from django.db.models.functions import ExtractYear, ExtractMonth
from django.utils import timezone

from core.models import Prodi
from pendaftaran.models import Pendaftar, Registrasi, Alamat, Sekolah
from umum.models import UmumDaftar
from raport.models import RaportBerkas
from beasiswa.models import BeasiswaDaftar


# ── Auth ────────────────────────────────────────────────────────────────────

def _require_token(view):
    @wraps(view)
    def inner(request, *args, **kwargs):
        expected = os.environ.get('PMB_API_TOKEN', '')
        if not expected or request.headers.get('X-PMB-Token') != expected:
            return JsonResponse({'error': 'Unauthorized'}, status=401)
        return view(request, *args, **kwargs)
    return inner


# ── Shared helpers ────────────────────────────────────────────────────────────

def _prodi_stats(jenjang_list):
    """
    Return list of per-prodi dicts for the given jenjang values.
    Uses ~8 queries total regardless of number of programs.
    """
    prodis = list(
        Prodi.objects.filter(jenjang__in=jenjang_list, aktif=True)
        .annotate(pendaftar_count=Count('pendaftar_prodi1', distinct=True))
        .order_by('fakultas', 'nama')
    )
    if not prodis:
        return []

    prodi_ids = [p.id for p in prodis]

    # diterima per prodi_lulus
    umum_dit = dict(
        UmumDaftar.objects.filter(status=2, prodi_lulus_id__in=prodi_ids)
        .values('prodi_lulus_id').annotate(n=Count('id'))
        .values_list('prodi_lulus_id', 'n')
    )
    raport_dit = dict(
        RaportBerkas.objects.filter(status=3, prodi_lulus_id__in=prodi_ids)
        .values('prodi_lulus_id').annotate(n=Count('id'))
        .values_list('prodi_lulus_id', 'n')
    )
    beasiswa_dit = dict(
        BeasiswaDaftar.objects.filter(status_seleksi=3, prodi_lulus_id__in=prodi_ids)
        .values('prodi_lulus_id').annotate(n=Count('id'))
        .values_list('prodi_lulus_id', 'n')
    )

    # accepted pendaftar_id sets per prodi (for registrasi lookup)
    accepted_by_prodi = {pid: set() for pid in prodi_ids}
    for row in UmumDaftar.objects.filter(status=2, prodi_lulus_id__in=prodi_ids).values('prodi_lulus_id', 'pendaftar_id'):
        accepted_by_prodi[row['prodi_lulus_id']].add(row['pendaftar_id'])
    for row in RaportBerkas.objects.filter(status=3, prodi_lulus_id__in=prodi_ids).values('prodi_lulus_id', 'pendaftar_id'):
        accepted_by_prodi[row['prodi_lulus_id']].add(row['pendaftar_id'])
    for row in BeasiswaDaftar.objects.filter(status_seleksi=3, prodi_lulus_id__in=prodi_ids).values('prodi_lulus_id', 'pendaftar_id'):
        accepted_by_prodi[row['prodi_lulus_id']].add(row['pendaftar_id'])

    all_accepted = set().union(*accepted_by_prodi.values()) if any(accepted_by_prodi.values()) else set()
    registered_ids = (
        set(Registrasi.objects.filter(pendaftar_id__in=all_accepted, status=1).values_list('pendaftar_id', flat=True))
        if all_accepted else set()
    )

    result = []
    for p in prodis:
        pid = p.id
        diterima = umum_dit.get(pid, 0) + raport_dit.get(pid, 0) + beasiswa_dit.get(pid, 0)
        result.append({
            'nama': p.nama,
            'jenjang': p.jenjang,
            'kuota': p.kuota,
            'pendaftar': p.pendaftar_count,
            'diterima': diterima,
            'registrasi': len(accepted_by_prodi[pid] & registered_ids),
        })
    return result


def _monthly_cumulative(model_field_year_filter, extract_field, year, now_dt):
    """
    Build a 12-element list: cumulative counts up to the current month,
    None for future months.  model_field_year_filter is already filtered to year.
    """
    monthly = dict(
        model_field_year_filter
        .annotate(_m=ExtractMonth(extract_field))
        .values('_m').annotate(n=Count('id'))
        .values_list('_m', 'n')
    )
    result, running = [], 0
    for m in range(1, 13):
        if year < now_dt.year or (year == now_dt.year and m <= now_dt.month):
            running += monthly.get(m, 0)
            result.append(running)
        else:
            result.append(None)
    return result


def _derive_status(p):
    """Derive display status string from a prefetched Pendaftar."""
    try:
        reg = p.registrasi
        if reg.status == 1:
            return 'Registrasi'
        if reg.status == 2:
            return 'Undur Diri'
    except Registrasi.DoesNotExist:
        pass

    if p.jalur == 'umum':
        try:
            if p.umum_daftar.status == 2:
                return 'Diterima'
        except UmumDaftar.DoesNotExist:
            pass
    elif p.jalur == 'raport':
        try:
            rb = p.raport_berkas
            if rb.status == 6:
                return 'Undur Diri'
            if rb.status == 3:
                return 'Diterima'
        except RaportBerkas.DoesNotExist:
            pass
    elif p.jalur == 'beasiswa':
        try:
            if p.beasiswa_daftar.status_seleksi == 3:
                return 'Diterima'
        except BeasiswaDaftar.DoesNotExist:
            pass

    return 'Pending'


def _derive_jalur_label(p):
    if p.jalur == 'raport':
        return 'Prestasi'
    if p.jalur == 'umum':
        return 'Reguler'
    if p.jalur == 'beasiswa':
        try:
            return 'KIP' if p.beasiswa_daftar.jenis_beasiswa == 'KIP' else 'Beasiswa'
        except BeasiswaDaftar.DoesNotExist:
            return 'Beasiswa'
    return p.jalur


# ── API Endpoints ─────────────────────────────────────────────────────────────

@_require_token
def rekap(request):
    """S1 / D3 / D4 per-prodi stats."""
    data = _prodi_stats(['S1', 'D3', 'D4'])
    return JsonResponse({'prodis': data})


@_require_token
def rekap_pasca(request):
    """S2 / S3 per-prodi stats."""
    data = _prodi_stats(['S2', 'S3'])
    return JsonResponse({'prodis': data})


@_require_token
def program_studi(request):
    """Split program list: s1 (S1/D3/D4) and s2 (S2/S3)."""
    return JsonResponse({
        's1': _prodi_stats(['S1', 'D3', 'D4']),
        's2': _prodi_stats(['S2', 'S3']),
    })


@_require_token
def laporan_registrasi(request):
    """Monthly cumulative registrasi trend + breakdown by jalur."""
    now_dt = timezone.now()
    current_year = now_dt.year

    # Years to include: last 3 years up to current
    years = range(current_year - 2, current_year + 1)
    data_registrasi = {}
    for year in years:
        qs = Registrasi.objects.filter(status=1, tgl_registrasi__year=year, tgl_registrasi__isnull=False)
        data_registrasi[year] = _monthly_cumulative(qs, 'tgl_registrasi', year, now_dt)

    # Jalur breakdown — all-time registrasi (status=1) totals
    prestasi = Registrasi.objects.filter(status=1, pendaftar__jalur='raport').count()
    reguler = Registrasi.objects.filter(status=1, pendaftar__jalur='umum').count()

    kip_pd_ids = BeasiswaDaftar.objects.filter(jenis_beasiswa='KIP').values_list('pendaftar_id', flat=True)
    kip = Registrasi.objects.filter(status=1, pendaftar__jalur='beasiswa', pendaftar_id__in=kip_pd_ids).count()
    beasiswa = Registrasi.objects.filter(status=1, pendaftar__jalur='beasiswa').count() - kip

    jalur = [
        {'nama': 'Prestasi', 'jumlah': prestasi},
        {'nama': 'Reguler', 'jumlah': reguler},
        {'nama': 'Beasiswa', 'jumlah': beasiswa},
        {'nama': 'KIP', 'jumlah': kip},
    ]

    return JsonResponse({
        'tahun_sekarang': current_year,
        'data_registrasi': data_registrasi,
        'jalur': jalur,
    })


@_require_token
def data_detail(request):
    """Flat applicant list with derived status and jalur label."""
    pendaftar_qs = (
        Pendaftar.objects
        .select_related('prodi1', 'sekolah', 'registrasi', 'umum_daftar', 'raport_berkas', 'beasiswa_daftar')
        .order_by('-created_at')
    )

    result = []
    for i, p in enumerate(pendaftar_qs, 1):
        try:
            asal = p.sekolah.nama
        except Sekolah.DoesNotExist:
            asal = '-'
        result.append({
            'no': i,
            'nama': p.nama,
            'asal': asal,
            'prodi': p.prodi1.nama if p.prodi1_id else '-',
            'jalur': _derive_jalur_label(p),
            'status': _derive_status(p),
        })

    return JsonResponse({'tahun_sekarang': timezone.now().year, 'pendaftar': result})


@_require_token
def perbandingan_tahun(request):
    """Yearly stats (6 years) + monthly cumulative pendaftar counts."""
    now_dt = timezone.now()
    current_year = now_dt.year
    years = range(current_year - 5, current_year + 1)

    perbandingan = []
    kumulatif = {}

    for year in years:
        pd_ids = list(Pendaftar.objects.filter(created_at__year=year).values_list('id', flat=True))
        pendaftar_count = len(pd_ids)

        if pd_ids:
            diterima = (
                UmumDaftar.objects.filter(status=2, pendaftar_id__in=pd_ids).count()
                + RaportBerkas.objects.filter(status=3, pendaftar_id__in=pd_ids).count()
                + BeasiswaDaftar.objects.filter(status_seleksi=3, pendaftar_id__in=pd_ids).count()
            )
            registrasi = Registrasi.objects.filter(pendaftar_id__in=pd_ids, status=1).count()

            # Undur diri: union of raport.status=6 and registrasi.status=2 (deduped via set)
            raport_wd = set(RaportBerkas.objects.filter(status=6, pendaftar_id__in=pd_ids).values_list('pendaftar_id', flat=True))
            reg_wd = set(Registrasi.objects.filter(status=2, pendaftar_id__in=pd_ids).values_list('pendaftar_id', flat=True))
            undur_diri = len(raport_wd | reg_wd)
        else:
            diterima = registrasi = undur_diri = 0

        perbandingan.append({
            'tahun': year,
            'pendaftar': pendaftar_count,
            'diterima': diterima,
            'registrasi': registrasi,
            'undur_diri': undur_diri,
        })

        qs = Pendaftar.objects.filter(created_at__year=year)
        kumulatif[year] = _monthly_cumulative(qs, 'created_at', year, now_dt)

    return JsonResponse({'perbandingan': perbandingan, 'kumulatif': kumulatif})


@_require_token
def sebaran_domisili(request):
    """Province → applicant count, sorted descending."""
    rows = (
        Alamat.objects.exclude(provinsi__isnull=True)
        .values('provinsi__nama')
        .annotate(count=Count('id'))
        .order_by('-count')
    )
    domisili = {row['provinsi__nama']: row['count'] for row in rows}
    return JsonResponse({'domisili': domisili, 'total': sum(domisili.values())})


@_require_token
def sebaran_sekolah(request):
    """School name → applicant count, sorted descending."""
    rows = (
        Sekolah.objects.values('nama')
        .annotate(count=Count('id'))
        .order_by('-count')
    )
    sekolah = {row['nama']: row['count'] for row in rows}
    return JsonResponse({'sekolah': sekolah, 'total': sum(sekolah.values())})
