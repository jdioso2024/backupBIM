from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.decorators import login_required, user_passes_test
from django.contrib.auth.models import User
from django.contrib import messages
from django.urls import reverse
from django.utils import timezone
from django.utils.http import url_has_allowed_host_and_scheme
from django.db.models import Q
from django.views.decorators.http import require_POST

import logging
from decimal import Decimal, InvalidOperation

logger = logging.getLogger(__name__)


def _push_tagihan_lulus(request, pendaftar):
    """Best-effort: buat tagihan DPP1 + SPP awal di keuangan saat lulus,
    tampilkan info No. VA. Tidak menggagalkan penyimpanan kelulusan."""
    try:
        from core.keuangan_client import kirim_tagihan_kelulusan
        hasil = kirim_tagihan_kelulusan(pendaftar)
        if hasil and hasil.get('nomor_va'):
            messages.info(
                request,
                f'Tagihan DPP Cicilan 1 & SPP Cicilan 1 dibuat di Sistem Keuangan. '
                f'No. VA mahasiswa: {hasil["nomor_va"]}',
            )
        elif hasil and hasil.get('_error'):
            messages.warning(
                request,
                f'Tagihan kuliah belum dibuat: {hasil["detail"]}',
            )
    except Exception:
        logger.exception('Gagal push tagihan kelulusan %s',
                         getattr(pendaftar, 'no_daftar', '?'))

from pendaftaran.models import Pendaftar, Registrasi
from raport.models import RaportBerkas
from beasiswa.models import BeasiswaDaftar, JenisBeasiswa
from beasiswa.forms import JenisBeasiswaForm
from umum.models import UmumDaftar
from core.models import Prodi, SiteSetting, BiayaKuliahPeriode, BiayaKuliahProdi, IntegrasiConfig
from core.forms import SiteSettingForm, IntegrasiConfigForm


def is_staff(user):
    return user.is_staff or user.is_superuser

staff_required = user_passes_test(is_staff, login_url='/panel/login/')
superuser_required = user_passes_test(lambda u: u.is_superuser, login_url='/panel/login/')


# ── Login / Logout Admin ──────────────────────────────────────────────────────

def login_admin(request):
    if request.user.is_authenticated and is_staff(request.user):
        return redirect('panel:dashboard')
    if request.method == 'POST':
        username = request.POST.get('username', '').strip()
        password = request.POST.get('password', '')
        user = authenticate(request, username=username, password=password)
        if user and is_staff(user):
            login(request, user)
            next_url = request.GET.get('next') or request.POST.get('next')
            if next_url and url_has_allowed_host_and_scheme(
                next_url,
                allowed_hosts={request.get_host()},
                require_https=request.is_secure(),
            ):
                return redirect(next_url)
            return redirect('panel:dashboard')
        if user and not is_staff(user):
            messages.error(request, 'Akun ini bukan akun staff/admin.')
        else:
            messages.error(request, 'Username atau password salah.')
    return render(request, 'panel/login.html')


def logout_admin(request):
    logout(request)
    return redirect('panel:login')


# ── Login As Cama (Impersonation) ─────────────────────────────────────────────

IMPERSONATE_SESSION_KEY  = 'impersonator_id'
IMPERSONATE_NAME_KEY     = 'impersonator_username'


@require_POST
@login_required
@superuser_required
def login_as(request, pk):
    """Superuser login sebagai akun pendaftar (impersonation).

    Simpan admin user id di session supaya bisa kembali via exit_login_as.
    Hanya superuser yang boleh — jangan diberikan ke staff biasa karena
    bisa dipakai mengakses data cama tanpa jejak.
    """
    if request.session.get(IMPERSONATE_SESSION_KEY):
        messages.error(request, 'Anda sedang impersonate user lain. Keluar dulu sebelum login as cama baru.')
        return redirect('panel:cari_cama_detail', pk=pk)

    pendaftar = get_object_or_404(Pendaftar.objects.select_related('user'), pk=pk)
    target_user = pendaftar.user
    if target_user.is_superuser:
        messages.error(request, 'Tidak diizinkan login as akun superuser lain.')
        return redirect('panel:cari_cama_detail', pk=pk)

    # Simpan info admin sebelum login() me-rotate session key
    impersonator_id       = request.user.pk
    impersonator_username = request.user.get_full_name() or request.user.username

    target_user.backend = 'django.contrib.auth.backends.ModelBackend'
    login(request, target_user)

    # login() cycle_key tapi pertahankan data session, jadi aman set di sini
    request.session[IMPERSONATE_SESSION_KEY] = impersonator_id
    request.session[IMPERSONATE_NAME_KEY]    = impersonator_username

    messages.info(
        request,
        f'Anda sekarang login sebagai {pendaftar.nama} ({target_user.email}). '
        f'Klik "Keluar dari Mode Login As" untuk kembali ke akun admin.'
    )
    return redirect('dashboard')


@require_POST
@login_required
def exit_login_as(request):
    """Kembali ke akun admin asli setelah impersonation."""
    imp_id = request.session.get(IMPERSONATE_SESSION_KEY)
    if not imp_id:
        messages.error(request, 'Anda tidak sedang dalam mode Login As.')
        return redirect('dashboard')

    try:
        admin = User.objects.get(pk=imp_id)
    except User.DoesNotExist:
        # Admin user hilang — paksa logout
        logout(request)
        messages.error(request, 'Akun admin asli sudah tidak ada. Silakan login ulang.')
        return redirect('panel:login')

    admin.backend = 'django.contrib.auth.backends.ModelBackend'
    login(request, admin)
    request.session.pop(IMPERSONATE_SESSION_KEY, None)
    request.session.pop(IMPERSONATE_NAME_KEY, None)

    messages.success(request, f'Sudah kembali ke akun admin ({admin.username}).')
    return redirect('panel:dashboard')


def _proses_meta(request, obj):
    obj.diproses_oleh = request.user.get_full_name() or request.user.username
    obj.tgl_diproses  = timezone.now()


# ── Dashboard ─────────────────────────────────────────────────────────────────

@login_required
@staff_required
def dashboard(request):
    from django.db.models import Count

    raport_lulus   = RaportBerkas.objects.filter(status=3).count()
    beasiswa_lolos = BeasiswaDaftar.objects.filter(status_seleksi=3).count()
    umum_lulus     = UmumDaftar.objects.filter(status=2).count()

    # Jumlah pendaftar per prodi (berdasarkan pilihan pertama)
    counts = {r['prodi1']: r['c'] for r in
              Pendaftar.objects.values('prodi1').annotate(c=Count('id'))}
    prodi_stats = []
    for p in Prodi.objects.filter(aktif=True).order_by('fakultas', 'nama'):
        jumlah = counts.get(p.pk, 0)
        kuota  = p.kuota or 0
        if kuota > 0:
            pct = min(round(jumlah / kuota * 100), 100)
        else:
            pct = 0
        prodi_stats.append({
            'nama':   p.nama,
            'kode':   p.kode,
            'jumlah': jumlah,
            'kuota':  kuota,
            'pct':    pct,
            'sisa':   max(0, kuota - jumlah) if kuota else None,
            'over':   (jumlah > kuota) if kuota else False,
        })
    prodi_stats.sort(key=lambda x: x['jumlah'], reverse=True)
    total_prodi_pendaftar = sum(x['jumlah'] for x in prodi_stats)
    for x in prodi_stats:
        x['pct_total'] = round(x['jumlah'] / total_prodi_pendaftar * 100) if total_prodi_pendaftar else 0

    # Registrasi ulang per jalur (status: 0=belum, 1=sudah, 2=batal)
    reg_map = {(r['pendaftar__jalur'], r['status']): r['c']
               for r in Registrasi.objects.values('pendaftar__jalur', 'status').annotate(c=Count('id'))}

    def reg(j, s):
        return reg_map.get((j, s), 0)

    # Jumlah pendaftar per jalur (dari Pendaftar, supaya total cocok dengan kartu)
    pend_by_jalur = {r['jalur']: r['c'] for r in
                     Pendaftar.objects.values('jalur').annotate(c=Count('id'))}

    jalur_rows = [
        {'nama': 'Raport', 'icon': 'fa-file-text', 'color': 'font-blue', 'jalur': 'raport',
         'total': pend_by_jalur.get('raport', 0),
         'diproses': RaportBerkas.objects.filter(status=1).count(),
         'valid': RaportBerkas.objects.filter(status=2).count(),
         'lulus': raport_lulus},
        {'nama': 'Beasiswa', 'icon': 'fa-trophy', 'color': 'font-purple', 'jalur': 'beasiswa',
         'total': pend_by_jalur.get('beasiswa', 0),
         'diproses': BeasiswaDaftar.objects.filter(status_seleksi=1).count(),
         'valid': BeasiswaDaftar.objects.filter(status_seleksi=2).count(),
         'lulus': beasiswa_lolos},
        {'nama': 'Umum (CBT)', 'icon': 'fa-laptop', 'color': 'font-green', 'jalur': 'umum',
         'total': pend_by_jalur.get('umum', 0),
         'diproses': None, 'valid': None,
         'lulus': umum_lulus},
    ]
    for row in jalur_rows:
        j = row['jalur']
        row['belum_du'] = reg(j, 0)
        row['sudah_du'] = reg(j, 1)
        row['batal_du'] = reg(j, 2)

    def csum(key):
        return sum((r[key] or 0) for r in jalur_rows)

    jalur_totals = {k: csum(k) for k in
                    ('total', 'diproses', 'valid', 'lulus', 'belum_du', 'sudah_du', 'batal_du')}

    # Distribusi pendaftar Beasiswa per jenis (untuk modal di angka Total Beasiswa)
    jenis_map = {j.kode: j.nama for j in JenisBeasiswa.objects.all()}
    bd_agg = (BeasiswaDaftar.objects
              .values('jenis_beasiswa', 'status_seleksi')
              .annotate(c=Count('id')))
    acc = {}
    for row in bd_agg:
        kode = row['jenis_beasiswa'] or ''
        s    = row['status_seleksi']
        n    = row['c']
        a = acc.setdefault(kode, {
            'kode':  kode,
            'nama':  jenis_map.get(kode) or (kode if kode else '— Tanpa jenis —'),
            'total': 0, 'diproses': 0, 'valid': 0, 'lolos': 0,
        })
        a['total'] += n
        if   s == 1: a['diproses'] += n
        elif s == 2: a['valid']    += n
        elif s == 3: a['lolos']    += n
    beasiswa_distribusi = sorted(acc.values(), key=lambda x: (-x['total'], x['nama']))
    beasiswa_dist_totals = {
        'total':    sum(x['total']    for x in beasiswa_distribusi),
        'diproses': sum(x['diproses'] for x in beasiswa_distribusi),
        'valid':    sum(x['valid']    for x in beasiswa_distribusi),
        'lolos':    sum(x['lolos']    for x in beasiswa_distribusi),
    }

    ctx = {
        'prodi_stats':       prodi_stats,
        'jalur_rows':        jalur_rows,
        'jalur_totals':      jalur_totals,
        'total_pendaftar':   Pendaftar.objects.count(),
        'total_lulus':       raport_lulus + beasiswa_lolos + umum_lulus,
        'menunggu_validasi': (jalur_rows[0]['diproses'] or 0) + (jalur_rows[1]['diproses'] or 0),
        'reg_sudah':         jalur_totals['sudah_du'],
        'beasiswa_distribusi':  beasiswa_distribusi,
        'beasiswa_dist_totals': beasiswa_dist_totals,
    }
    return render(request, 'panel/dashboard.html', ctx)


# ── Validasi Dokumen ──────────────────────────────────────────────────────────

@login_required
@staff_required
def validasi_list(request):
    jalur = request.GET.get('jalur', 'raport')
    q     = request.GET.get('q', '')

    if jalur == 'raport':
        qs = RaportBerkas.objects.select_related('pendaftar', 'pendaftar__prodi1').order_by('status', 'pendaftar__nama')
        if q:
            qs = qs.filter(Q(pendaftar__nama__icontains=q) | Q(pendaftar__no_daftar__icontains=q))
    elif jalur == 'beasiswa':
        qs = BeasiswaDaftar.objects.select_related('pendaftar', 'pendaftar__prodi1').order_by('status_seleksi', 'pendaftar__nama')
        if q:
            qs = qs.filter(Q(pendaftar__nama__icontains=q) | Q(pendaftar__no_daftar__icontains=q))
    else:
        qs = UmumDaftar.objects.select_related('pendaftar', 'pendaftar__prodi1').order_by('status', 'pendaftar__nama')
        if q:
            qs = qs.filter(Q(pendaftar__nama__icontains=q) | Q(pendaftar__no_daftar__icontains=q))

    return render(request, 'panel/validasi_list.html', {'object_list': qs, 'jalur': jalur, 'q': q})


@login_required
@staff_required
def validasi_detail(request, jalur, pk):
    prodi_list = Prodi.objects.filter(aktif=True).order_by('fakultas', 'nama')

    if jalur == 'raport':
        obj = get_object_or_404(RaportBerkas, pk=pk)
        if request.method == 'POST':
            obj.status         = int(request.POST.get('status', obj.status))
            obj.keterangan     = request.POST.get('keterangan', '')
            obj.nilai_raport   = request.POST.get('nilai_raport') or None
            obj.nilai_prestasi = request.POST.get('nilai_prestasi') or None
            prodi_id = request.POST.get('prodi_lulus')
            obj.prodi_lulus = Prodi.objects.filter(pk=prodi_id).first() if prodi_id else None
            _proses_meta(request, obj)
            obj.save()
            messages.success(request, 'Status berkas raport diperbarui.')
            return redirect('panel:validasi_list')
    elif jalur == 'beasiswa':
        obj = get_object_or_404(BeasiswaDaftar, pk=pk)
        if request.method == 'POST':
            jenis_kode = (request.POST.get('jenis_beasiswa', '') or '').strip()
            if jenis_kode and JenisBeasiswa.objects.filter(kode=jenis_kode).exists():
                obj.jenis_beasiswa = jenis_kode
            obj.status_seleksi  = int(request.POST.get('status_seleksi', obj.status_seleksi))
            obj.catatan_panitia = request.POST.get('catatan_panitia', '')
            prodi_id = request.POST.get('prodi_lulus')
            obj.prodi_lulus = Prodi.objects.filter(pk=prodi_id).first() if prodi_id else None
            _proses_meta(request, obj)
            obj.save()
            messages.success(request, 'Status seleksi beasiswa diperbarui.')
            return redirect('panel:validasi_list')
    else:
        obj = get_object_or_404(UmumDaftar, pk=pk)
        if request.method == 'POST':
            obj.status     = int(request.POST.get('status', obj.status))
            obj.skor_cbt   = request.POST.get('skor_cbt') or None
            obj.keterangan = request.POST.get('keterangan', '')
            prodi_id = request.POST.get('prodi_lulus')
            obj.prodi_lulus = Prodi.objects.filter(pk=prodi_id).first() if prodi_id else None
            _proses_meta(request, obj)
            obj.save()
            messages.success(request, 'Status CBT/Umum diperbarui.')
            return redirect('panel:validasi_list')

    jenis_beasiswa_list = (JenisBeasiswa.objects.filter(aktif=True).order_by('urutan', 'nama')
                           if jalur == 'beasiswa' else [])
    return render(request, 'panel/validasi_detail.html', {
        'obj': obj, 'jalur': jalur,
        'prodi_list': prodi_list,
        'jenis_beasiswa_list': jenis_beasiswa_list,
    })


# ── Kelulusan ─────────────────────────────────────────────────────────────────

@login_required
@staff_required
def kelulusan_list(request):
    jalur = request.GET.get('jalur', 'raport')
    q     = request.GET.get('q', '')

    if jalur == 'raport':
        prodi_id       = request.GET.get('prodi', '')
        pg_raw         = request.GET.get('pg', '')
        prodi_list_all = Prodi.objects.filter(aktif=True).order_by('fakultas', 'nama')

        # Hanya tampilkan yang belum diputuskan (status=2 Valid)
        qs = RaportBerkas.objects.select_related(
            'pendaftar', 'pendaftar__prodi1', 'pendaftar__prodi2', 'prodi_lulus'
        ).filter(status=2)
        if q:
            qs = qs.filter(Q(pendaftar__nama__icontains=q) | Q(pendaftar__no_daftar__icontains=q))
        if prodi_id:
            qs = qs.filter(
                Q(pendaftar__prodi1__pk=prodi_id) | Q(pendaftar__prodi2__pk=prodi_id)
            )

        passing_grade    = None
        list_lulus       = []
        list_tidak_lulus = []
        if pg_raw:
            try:
                passing_grade = Decimal(pg_raw.replace(',', '.'))
                for rb in qs:
                    nilai = rb.nilai_raport or Decimal('0')
                    if nilai >= passing_grade:
                        list_lulus.append(rb)
                    else:
                        list_tidak_lulus.append(rb)
            except InvalidOperation:
                passing_grade = None

        # Bulk POST: proses kelulusan sekaligus
        if request.method == 'POST':
            prodi_lulus_id  = request.POST.get('prodi_lulus', '')
            prodi_lulus_obj = Prodi.objects.filter(pk=prodi_lulus_id).first() if prodi_lulus_id else None

            for rb_pk in request.POST.getlist('list_lulus'):
                rb = RaportBerkas.objects.filter(pk=rb_pk).first()
                if rb:
                    rb.status      = 3  # Lulus
                    rb.prodi_lulus = prodi_lulus_obj
                    _proses_meta(request, rb)
                    rb.save()
                    Registrasi.objects.get_or_create(pendaftar=rb.pendaftar)

            for rb_pk in request.POST.getlist('list_tidak_lulus'):
                rb = RaportBerkas.objects.filter(pk=rb_pk).first()
                if rb:
                    rb.status = 4  # Tidak Lulus
                    _proses_meta(request, rb)
                    rb.save()

            total = len(request.POST.getlist('list_lulus')) + len(request.POST.getlist('list_tidak_lulus'))
            messages.success(request, f'{total} data kelulusan raport berhasil diproses.')
            return redirect(f'{request.path}?jalur=raport')

        prodi_terpilih = Prodi.objects.filter(pk=prodi_id).first() if prodi_id else None
        return render(request, 'panel/kelulusan_list.html', {
            'jalur':           jalur,
            'q':               q,
            'prodi_list_all':  prodi_list_all,
            'prodi_id':        prodi_id,
            'prodi_terpilih':  prodi_terpilih,
            'pg_raw':          pg_raw,
            'passing_grade':   passing_grade,
            'list_lulus':      list_lulus,
            'list_tidak_lulus': list_tidak_lulus,
            'object_list':     list(qs) if not passing_grade else [],
        })

    elif jalur == 'beasiswa':
        qs = BeasiswaDaftar.objects.select_related('pendaftar', 'prodi_lulus').filter(
            status_seleksi__in=[2, 3, 4]
        ).order_by('status_seleksi', 'pendaftar__nama')
    else:
        qs = UmumDaftar.objects.select_related('pendaftar', 'prodi_lulus').order_by('status', 'pendaftar__nama')

    if q:
        qs = qs.filter(Q(pendaftar__nama__icontains=q) | Q(pendaftar__no_daftar__icontains=q))
    return render(request, 'panel/kelulusan_list.html', {'object_list': qs, 'jalur': jalur, 'q': q})


@login_required
@staff_required
def kelulusan_set(request, jalur, pk):
    prodi_list = Prodi.objects.filter(aktif=True).order_by('fakultas', 'nama')

    def _set_kelas_if_sent(pendaftar):
        """Kalau admin kirim 'kelas' di form, set juga ke Registrasi cama.
        Dipanggil setelah get_or_create(Registrasi) di bawah."""
        kelas = request.POST.get('kelas', '').strip()
        valid = {k for k, _ in Registrasi._meta.get_field('kelas').choices if k}
        if kelas and kelas in valid:
            reg, _ = Registrasi.objects.get_or_create(pendaftar=pendaftar)
            reg.kelas = kelas
            reg.save(update_fields=['kelas'])

    if jalur == 'raport':
        obj = get_object_or_404(RaportBerkas, pk=pk)
        if request.method == 'POST':
            obj.status      = int(request.POST.get('status', obj.status))
            obj.keterangan  = request.POST.get('keterangan', obj.keterangan)
            prodi_id = request.POST.get('prodi_lulus')
            obj.prodi_lulus = Prodi.objects.filter(pk=prodi_id).first() if prodi_id else None
            _proses_meta(request, obj)
            obj.save()
            if obj.status == 3:
                Registrasi.objects.get_or_create(pendaftar=obj.pendaftar)
                _set_kelas_if_sent(obj.pendaftar)
                _push_tagihan_lulus(request, obj.pendaftar)
            messages.success(request, f'Kelulusan {obj.pendaftar.nama} disimpan.')
            return redirect('panel:kelulusan_list')
    elif jalur == 'beasiswa':
        obj = get_object_or_404(BeasiswaDaftar, pk=pk)
        if request.method == 'POST':
            obj.status_seleksi  = int(request.POST.get('status_seleksi', obj.status_seleksi))
            obj.catatan_panitia = request.POST.get('catatan_panitia', obj.catatan_panitia)
            prodi_id = request.POST.get('prodi_lulus')
            obj.prodi_lulus = Prodi.objects.filter(pk=prodi_id).first() if prodi_id else None
            _proses_meta(request, obj)
            obj.save()
            if obj.status_seleksi == 3:
                Registrasi.objects.get_or_create(pendaftar=obj.pendaftar)
                _set_kelas_if_sent(obj.pendaftar)
                _push_tagihan_lulus(request, obj.pendaftar)
            messages.success(request, f'Kelulusan {obj.pendaftar.nama} disimpan.')
            return redirect('panel:kelulusan_list')
    else:
        obj = get_object_or_404(UmumDaftar, pk=pk)
        if request.method == 'POST':
            obj.status      = int(request.POST.get('status', obj.status))
            obj.skor_cbt    = request.POST.get('skor_cbt') or obj.skor_cbt
            obj.keterangan  = request.POST.get('keterangan', obj.keterangan)
            prodi_id = request.POST.get('prodi_lulus')
            obj.prodi_lulus = Prodi.objects.filter(pk=prodi_id).first() if prodi_id else None
            _proses_meta(request, obj)
            obj.save()
            if obj.status == 2:
                Registrasi.objects.get_or_create(pendaftar=obj.pendaftar)
                _set_kelas_if_sent(obj.pendaftar)
                _push_tagihan_lulus(request, obj.pendaftar)
            messages.success(request, f'Kelulusan {obj.pendaftar.nama} disimpan.')
            return redirect('panel:kelulusan_list')

    # Info kelas saat ini (kalau Registrasi sudah ada — tampil di form supaya admin
    # tahu cama sudah pilih atau belum)
    reg = Registrasi.objects.filter(pendaftar=obj.pendaftar).first()
    return render(request, 'panel/kelulusan_set.html', {
        'obj': obj, 'jalur': jalur, 'prodi_list': prodi_list,
        'reg': reg,
        'kelas_choices': [(k, l) for k, l in Registrasi._meta.get_field('kelas').choices if k],
    })


# ── Registrasi Ulang ──────────────────────────────────────────────────────────

def _generate_nim(reg):
    prodi = reg.prodi_lulus
    if not prodi:
        return ''
    prefix = prodi.kode
    year   = str(timezone.now().year)[2:]
    existing = Registrasi.objects.filter(
        status=1, nim__startswith=prefix + year
    ).values_list('nim', flat=True)
    orders = []
    for n in existing:
        suffix = n[len(prefix) + 2:]
        try:
            orders.append(int(suffix.lstrip('0') or '0'))
        except ValueError:
            pass
    order = (max(orders) + 1) if orders else 1
    return prefix + year + '0' + str(order).zfill(3)


@login_required
@staff_required
def registrasi_list(request):
    q             = request.GET.get('q', '')
    status_filter = request.GET.get('status', '')
    qs = Registrasi.objects.select_related(
        'pendaftar', 'pendaftar__prodi1'
    ).order_by('status', 'pendaftar__nama')
    if q:
        qs = qs.filter(
            Q(pendaftar__nama__icontains=q) |
            Q(pendaftar__no_daftar__icontains=q) |
            Q(nim__icontains=q)
        )
    if status_filter != '':
        qs = qs.filter(status=int(status_filter))
    return render(request, 'panel/registrasi_list.html', {
        'object_list': qs, 'q': q, 'status_filter': status_filter,
    })


@login_required
@staff_required
def registrasi_set(request, pk):
    obj = get_object_or_404(Registrasi, pk=pk)
    if request.method == 'POST':
        nim     = request.POST.get('nim', '').strip()
        catatan = request.POST.get('catatan', '')
        status  = int(request.POST.get('status', obj.status))
        kelas   = request.POST.get('kelas', obj.kelas) or 'reguler'

        # Auto-generate NIM jika status selesai dan NIM kosong
        if status == 1 and not nim:
            nim = _generate_nim(obj)

        # Batal / belum selesai → NIM dikosongkan
        if status != 1:
            nim = ''

        obj.status  = status
        obj.kelas   = kelas
        obj.nim     = nim
        obj.catatan = catatan
        if obj.status == 1 and not obj.tgl_registrasi:
            obj.tgl_registrasi = timezone.now()
        elif obj.status != 1:
            obj.tgl_registrasi = None
        obj.diproses_oleh = request.user.get_full_name() or request.user.username
        obj.save()
        messages.success(request, f'Registrasi {obj.pendaftar.nama} diperbarui.')
        return redirect('panel:registrasi_set', pk=pk)
    return render(request, 'panel/registrasi_set.html', {'obj': obj, 'kelas_choices': Registrasi._meta.get_field('kelas').choices})


# ── PDF Dokumen ───────────────────────────────────────────────────────────────

def _get_lulus_info(reg):
    """Return (prodi, tgl_diproses, nilai) from the jalur-specific model."""
    pendaftar = reg.pendaftar
    try:
        if pendaftar.jalur == 'raport':
            rb = pendaftar.raport_berkas
            if rb.status == 3:
                return rb.prodi_lulus, rb.tgl_diproses, rb.nilai_raport
        elif pendaftar.jalur == 'beasiswa':
            bs = pendaftar.beasiswa_daftar
            if bs.status_seleksi == 3:
                return bs.prodi_lulus, bs.tgl_diproses, None
        elif pendaftar.jalur == 'umum':
            ud = pendaftar.umum_daftar
            if ud.status == 2:
                return ud.prodi_lulus, ud.tgl_diproses, ud.skor_cbt
    except Exception:
        pass
    return reg.prodi_lulus, None, None


def _draw_kop(c, site, base_dir):
    """Gambar kop dokumen pada canvas PDF. Kop line ada di y=748."""
    import os
    logo_path = os.path.join(base_dir, 'static/assets/pages/img/logo-ums-baru-kecil.png')
    if site.logo_kop:
        try:
            if os.path.isfile(site.logo_kop.path):
                logo_path = site.logo_kop.path
        except Exception:
            pass
    elif site.logo:
        try:
            if os.path.isfile(site.logo.path):
                logo_path = site.logo.path
        except Exception:
            pass
    if os.path.isfile(logo_path):
        c.drawImage(logo_path, 30, 752, width=200, height=60, mask='auto')
    c.setFillColorRGB(0.075, 0.6, 0.882)
    c.setFont('Helvetica-Bold', 14)
    c.drawRightString(565, 800, site.nama_universitas)
    c.setFont('Helvetica', 10)
    c.drawRightString(565, 785, site.alamat)
    parts = []
    if site.telepon:
        parts.append('Telp. ' + site.telepon)
    if site.website:
        parts.append(site.website)
    if parts:
        c.drawRightString(565, 771, '  |  '.join(parts))
    c.setFillColorRGB(0, 0, 0)
    c.line(30, 748, 565, 748)


@login_required
@staff_required
def pdf_sertifikat_lulus(request, pk):
    import os
    from io import BytesIO
    from datetime import datetime
    from django.http import HttpResponse
    from django.conf import settings as dj_settings
    from reportlab.pdfgen import canvas
    from reportlab.lib.pagesizes import A4
    from reportlab.graphics.shapes import Drawing
    from reportlab.graphics.barcode.qr import QrCodeWidget
    from reportlab.graphics import renderPDF

    reg = get_object_or_404(Registrasi, pk=pk)
    pendaftar = reg.pendaftar
    prodi, tgl_diproses, nilai = _get_lulus_info(reg)

    if not prodi:
        messages.error(request, 'Data kelulusan (prodi lulus) belum tersedia.')
        return redirect('panel:registrasi_set', pk=pk)

    if not reg.kelas:
        messages.error(request, 'Kelas belum dipilih. Pilih kelas terlebih dahulu sebelum cetak sertifikat.')
        return redirect('panel:registrasi_set', pk=pk)

    site = SiteSetting.get_instance()

    foto_path = os.path.join(dj_settings.BASE_DIR, 'static/assets/pages/img/User-Default.jpg')
    if pendaftar.pas_foto:
        try:
            if os.path.isfile(pendaftar.pas_foto.path):
                foto_path = pendaftar.pas_foto.path
        except Exception:
            pass

    response = HttpResponse(content_type='application/pdf')
    response['Content-Disposition'] = f'inline; filename="Sertifikat_{pendaftar.no_daftar}.pdf"'

    buffer = BytesIO()
    c = canvas.Canvas(buffer, pagesize=A4)
    c.setTitle('SERTIFIKAT_' + pendaftar.no_daftar)

    _draw_kop(c, site, dj_settings.BASE_DIR)

    qrw = QrCodeWidget(pendaftar.no_daftar)
    b   = qrw.getBounds()
    qw, qh = b[2] - b[0], b[3] - b[1]
    d = Drawing(80, 80, transform=[80. / qw, 0, 0, 80. / qh, 0, 0])
    d.add(qrw)
    renderPDF.draw(d, c, 485, 645)

    if os.path.isfile(foto_path):
        c.drawImage(foto_path, 40, 550, width=115, height=160)

    c.setFont('Helvetica-Bold', 14)
    c.drawString(200, 726, 'SERTIFIKAT KELULUSAN')
    c.setFont('Helvetica', 10)
    c.drawString(180, 711, f'Penerimaan Mahasiswa Baru {pendaftar.get_jalur_display()}')
    # c.line(30, 701, 565, 701)

    lx, vx = 175, 295
    c.setFont('Courier', 12)
    y = 687
    c.drawString(lx, y, 'NOMOR');  c.drawString(vx, y, ':' + pendaftar.no_daftar);  y -= 16
    c.drawString(lx, y, 'NAMA');   c.drawString(vx, y, ':' + pendaftar.nama.upper()[:30]); y -= 16
    c.drawString(lx, y, 'STATUS'); c.drawString(vx, y, ':LULUS'); y -= 16

    prodi_nama = prodi.nama if prodi else '-'
    c.drawString(lx, y, 'PROGRAM STUDI')
    if len(prodi_nama) <= 25:
        c.drawString(vx, y, ':' + prodi_nama);  y -= 16
    else:
        c.drawString(vx, y, ':' + prodi_nama[:25]); y -= 14
        c.drawString(vx, y, prodi_nama[25:50]);      y -= 16

    if nilai:
        c.drawString(lx, y, 'NILAI');  c.drawString(vx, y, ':' + str(nilai));  y -= 16

    tgl_str = tgl_diproses.strftime('%d/%m/%Y') if tgl_diproses else '-'
    c.drawString(lx, y, 'TANGGAL LULUS');  c.drawString(vx, y, ':' + tgl_str); y -= 16
    if reg.nomor_va:
        c.drawString(lx, y, 'NO. VA');  c.drawString(vx, y, ':' + reg.nomor_va)

    kota_ttd = site.kota_ttd or site.kota or 'Surakarta'
    tgl_long = tgl_diproses.strftime('%d %B %Y') if tgl_diproses else '-'
    c.setFont('Helvetica', 11)
    c.drawString(55, 505, f'{kota_ttd}, {tgl_long}')
    c.drawString(55, 490, site.nama_program)
    c.line(55, 450, 240, 450)
    c.setFont('Helvetica', 10)
    c.drawString(55, 438, site.jabatan_pimpinan or 'Kepala Panitia PMB')

    c.setFont('Helvetica', 11)
    c.drawString(340, 505, f'{kota_ttd}, {tgl_long}')
    c.drawString(340, 490, 'Mengetahui,')
    c.line(340, 450, 525, 450)
    c.setFont('Helvetica', 10)
    c.drawString(340, 438, site.nama_pimpinan or site.jabatan_pimpinan or '')
    if site.nip_pimpinan:
        c.setFont('Helvetica', 9)
        c.drawString(340, 426, site.nip_pimpinan)

    c.line(30, 410, 565, 410)
    c.setFont('Courier-Oblique', 8)
    c.drawString(360, 398, 'Dicetak: ' + datetime.now().strftime('%d/%m/%Y %H:%M:%S'))

    # ── Halaman 2: Rincian biaya kuliah + keterangan ─────────────────────────
    periode = BiayaKuliahPeriode.get_aktif()
    biaya = None
    if periode:
        biaya = BiayaKuliahProdi.objects.filter(
            periode=periode, prodi=prodi, jenis_kelas=reg.kelas
        ).first()

    if periode and biaya:
        c.showPage()
        _draw_kop(c, site, dj_settings.BASE_DIR)

        c.setFont('Helvetica-Bold', 14)
        c.drawString(200, 726, 'RINCIAN BIAYA KULIAH')
        c.setFont('Helvetica', 10)
        c.drawString(150, 711,
                     f'{prodi.nama} — Kelas {reg.get_kelas_display()} — Periode {periode.tahun_pmb}')
        c.line(30, 701, 565, 701)

        # Biaya umum (pendaftaran + PKKMB)
        y = 685
        c.setFont('Helvetica-Bold', 11)
        c.drawString(40, y, 'Biaya Umum'); y -= 4
        c.line(40, y, 555, y); y -= 14

        def _fmt(n):
            return 'Rp ' + f'{n:,.0f}'.replace(',', '.')

        c.setFont('Courier', 11)
        c.drawString(50, y, 'Biaya Pendaftaran')
        c.drawRightString(540, y, _fmt(periode.biaya_pendaftaran)); y -= 16
        if periode.biaya_pkkmb:
            c.drawString(50, y, 'Biaya PKKMB')
            c.drawRightString(540, y, _fmt(periode.biaya_pkkmb)); y -= 16
        y -= 8

        # Rincian biaya per kelas
        c.setFont('Helvetica-Bold', 11)
        c.drawString(40, y, f'Biaya Kelas {reg.get_kelas_display()}'); y -= 4
        c.line(40, y, 555, y); y -= 14

        c.setFont('Courier', 11)
        if biaya.is_boarding:
            c.drawString(50, y, 'Pengembangan')
            c.drawRightString(540, y, _fmt(biaya.pengembangan)); y -= 16
            c.drawString(50, y, 'Biaya Hidup')
            c.drawRightString(540, y, _fmt(biaya.biaya_hidup)); y -= 16
            c.drawString(50, y, 'DPP & SPP')
            c.drawRightString(540, y, _fmt(biaya.dpp_spp_total)); y -= 24

            total_awal = periode.biaya_pendaftaran + periode.biaya_pkkmb + biaya.harga_boarding
            c.line(40, y + 14, 555, y + 14)
            c.setFont('Helvetica-Bold', 11)
            label_total = 'Total Pembayaran Awal (Pendaftaran' + (' + PKKMB' if periode.biaya_pkkmb else '') + ' + Registrasi)'
            c.drawString(50, y, label_total)
            c.drawRightString(540, y, _fmt(total_awal)); y -= 28
        else:
            c.drawString(50, y, 'DPP Cicilan 1')
            c.drawRightString(540, y, _fmt(biaya.dpp_cicilan_1)); y -= 16
            c.drawString(50, y, 'DPP Cicilan 2')
            c.drawRightString(540, y, _fmt(biaya.dpp_cicilan_2)); y -= 16
            c.drawString(50, y, 'SPP / Semester')
            c.drawRightString(540, y, _fmt(biaya.spp_per_semester)); y -= 16
            c.drawString(50, y, 'Biaya Saat Registrasi')
            c.drawRightString(540, y, _fmt(biaya.biaya_saat_registrasi)); y -= 24

            total_awal = periode.biaya_pendaftaran + periode.biaya_pkkmb + biaya.biaya_saat_registrasi
            c.line(40, y + 14, 555, y + 14)
            c.setFont('Helvetica-Bold', 11)
            label_total = 'Total Pembayaran Awal (Pendaftaran' + (' + PKKMB' if periode.biaya_pkkmb else '') + ' + Registrasi)'
            c.drawString(50, y, label_total)
            c.drawRightString(540, y, _fmt(total_awal)); y -= 28

        if reg.nomor_va:
            c.setFont('Helvetica-Bold', 11)
            c.drawString(40, y, 'Pembayaran via Virtual Account'); y -= 4
            c.line(40, y, 555, y); y -= 16
            c.setFont('Courier-Bold', 13)
            c.drawString(50, y, 'No. VA : ' + reg.nomor_va); y -= 14
            c.setFont('Helvetica', 8)
            c.drawString(50, y, 'Gunakan nomor Virtual Account ini untuk pembayaran (DPP, SPP, dll) '
                                'melalui bank mitra.'); y -= 18

        # Keterangan — font 8pt supaya poin 1-3 muat satu baris, poin 4 boleh wrap
        if periode.keterangan:
            c.setFont('Helvetica-Bold', 11)
            c.drawString(40, y, 'Keterangan'); y -= 4
            c.line(40, y, 555, y); y -= 12
            c.setFont('Helvetica', 8)
            from reportlab.lib.utils import simpleSplit
            for line in periode.keterangan.splitlines():
                wrapped = simpleSplit(line, 'Helvetica', 8, 520)
                for w in wrapped:
                    c.drawString(50, y, w); y -= 10
                y -= 2

        c.line(30, 50, 565, 50)
        c.setFont('Courier-Oblique', 8)
        c.drawString(360, 38, 'Dicetak: ' + datetime.now().strftime('%d/%m/%Y %H:%M:%S'))

    c.showPage()
    c.save()
    pdf = buffer.getvalue()
    buffer.close()
    response.write(pdf)
    return response


@login_required
@staff_required
def pdf_bukti_reg(request, pk):
    import os
    from io import BytesIO
    from datetime import datetime
    from django.http import HttpResponse
    from django.conf import settings as dj_settings
    from reportlab.pdfgen import canvas
    from reportlab.lib.pagesizes import A4
    from reportlab.graphics.shapes import Drawing
    from reportlab.graphics.barcode.qr import QrCodeWidget
    from reportlab.graphics import renderPDF

    reg = get_object_or_404(Registrasi, pk=pk)
    if not (reg.status == 1 and reg.nim):
        messages.error(request, 'Registrasi belum selesai atau NIM belum ada.')
        return redirect('panel:registrasi_set', pk=pk)

    pendaftar = reg.pendaftar
    prodi     = reg.prodi_lulus
    site      = SiteSetting.get_instance()

    foto_path = os.path.join(dj_settings.BASE_DIR, 'static/assets/pages/img/User-Default.jpg')
    for foto_src in [reg.pas_foto, pendaftar.pas_foto]:
        if foto_src:
            try:
                if os.path.isfile(foto_src.path):
                    foto_path = foto_src.path
                    break
            except Exception:
                pass

    response = HttpResponse(content_type='application/pdf')
    response['Content-Disposition'] = f'inline; filename="BuktiReg_{reg.nim}.pdf"'

    buffer = BytesIO()
    width, height = A4
    c = canvas.Canvas(buffer, pagesize=A4)
    c.setTitle('BUKTI_REGISTRASI_' + reg.nim)

    c.setFont('Courier-Oblique', 8)
    c.drawString(380, 828, 'Dicetak: ' + datetime.now().strftime('%d/%m/%Y %H:%M:%S'))

    _draw_kop(c, site, dj_settings.BASE_DIR)

    qrw = QrCodeWidget(reg.nim)
    b   = qrw.getBounds()
    qw, qh = b[2] - b[0], b[3] - b[1]
    d = Drawing(80, 80, transform=[80. / qw, 0, 0, 80. / qh, 0, 0])
    d.add(qrw)
    renderPDF.draw(d, c, 483, 640)

    if os.path.isfile(foto_path):
        c.drawImage(foto_path, 30, 633, width=80, height=97)

    c.setFont('Helvetica-Bold', 12)
    c.drawString(165, 726, 'BUKTI REGISTRASI / SURAT PENGANTAR')
    c.setFont('Helvetica', 10)
    c.drawString(190, 712, site.nama_program + ' ' + site.tahun_ajaran)

    c.setFont('Courier', 12)
    c.drawString(120, 698, 'NOMOR');         c.drawString(225, 698, ':' + pendaftar.no_daftar)
    c.drawString(120, 683, 'NAMA');          c.drawString(225, 683, ':' + pendaftar.nama.upper())
    ttl = ''
    if pendaftar.tempat_lahir and pendaftar.tanggal_lahir:
        ttl = pendaftar.tempat_lahir.upper() + ', ' + pendaftar.tanggal_lahir.strftime('%d %B %Y').upper()
    c.drawString(120, 668, 'TTL');           c.drawString(225, 668, ':' + ttl)
    c.drawString(120, 653, 'PROGRAM STUDI'); c.drawString(225, 653, ':' + (prodi.nama.upper() if prodi else '-'))
    c.drawString(120, 638, 'NIM');           c.drawString(225, 638, ':' + reg.nim)
    c.line(30, 628, 565, 628)

    c.setFont('Helvetica', 9)
    c.drawString(30, 618, '* PASTIKAN BIODATA ANDA BENAR, JIKA TERDAPAT KEKELIRUAN SEGERA HUBUNGI PANITIA PMB.')
    c.drawString(30, 604, '* KTM dan perlengkapan dapat diambil di bagian administrasi dengan menunjukkan:')

    items_kiri = [
        '1. Asli bukti registrasi ini;',
        '2. Asli sertifikat kelulusan hasil seleksi;',
        '3. Surat keterangan sehat (prodi yang mensyaratkan);',
        '4. 1 Fotokopi bukti pembayaran pendaftaran;',
        '5. 1 Fotokopi bukti pembayaran SKS;',
        '6. 1 Fotokopi bukti pembayaran pengembangan;',
    ]
    items_kanan = [
        '7. 1 Fotokopi KTP / Kartu Keluarga;',
        '8. 1 Fotokopi ijazah SMA/SMK yang dilegalisir;',
        '9. Rapor asli atau fotokopi legalisir;',
        '10. Akte kelahiran asli;',
        '11. SKHUN asli atau fotokopi legalisir;',
        '12. 2 lembar pas foto 4x6 terbaru;',
    ]
    yl, yr = 590, 590
    for item in items_kiri:
        c.drawString(32, yl, item);  yl -= 12
    for item in items_kanan:
        c.drawString(300, yr, item); yr -= 12

    y_bottom = min(yl, yr) - 8
    c.setFont('Helvetica-Bold', 9)
    c.drawString(30, y_bottom, 'URUTKAN SEMUA BERKAS DAN SERAHKAN KE BAGIAN REGISTRASI.')
    c.line(30, y_bottom - 14, 565, y_bottom - 14)

    c.showPage()
    c.save()
    pdf = buffer.getvalue()
    buffer.close()
    response.write(pdf)
    return response


@login_required
@staff_required
def pdf_ktm(request, pk):
    import os
    from io import BytesIO
    from django.http import HttpResponse
    from django.conf import settings as dj_settings
    from reportlab.pdfgen import canvas
    from reportlab.graphics.shapes import Drawing
    from reportlab.graphics.barcode.qr import QrCodeWidget
    from reportlab.graphics import renderPDF

    reg = get_object_or_404(Registrasi, pk=pk)
    if not (reg.status == 1 and reg.nim):
        messages.error(request, 'Registrasi belum selesai atau NIM belum ada.')
        return redirect('panel:registrasi_set', pk=pk)

    pendaftar = reg.pendaftar
    prodi     = reg.prodi_lulus
    site      = SiteSetting.get_instance()

    foto_path = os.path.join(dj_settings.BASE_DIR, 'static/assets/pages/img/User-Default.jpg')
    for foto_src in [reg.pas_foto, pendaftar.pas_foto]:
        if foto_src:
            try:
                if os.path.isfile(foto_src.path):
                    foto_path = foto_src.path
                    break
            except Exception:
                pass

    logo_path = os.path.join(dj_settings.BASE_DIR, 'static/assets/pages/img/logo-ums-baru-kecil.png')
    if site.logo:
        try:
            logo_path = site.logo.path
        except Exception:
            pass

    response = HttpResponse(content_type='application/pdf')
    response['Content-Disposition'] = f'inline; filename="KTM_{reg.nim}.pdf"'

    buffer = BytesIO()
    c = canvas.Canvas(buffer)
    c.setPageSize((240, 150))
    c.setTitle('KTM_' + reg.nim)
    c.setLineWidth(0.5)

    # halaman 1: area background KTM (kosong, untuk dicetak di atas template KTM)
    c.showPage()

    # halaman 2: data KTM depan
    # Logo kiri atas kecil
    if os.path.isfile(logo_path):
        c.drawImage(logo_path, 7, 105, width=100, height=30, mask='auto')

    c.setFillColorRGB(0.075, 0.6, 0.882)
    c.setFont('Helvetica-Bold', 5.5)
    # c.drawString(40, 132, site.singkatan)
    c.setFont('Helvetica', 4.5)
    # c.drawString(40, 125, site.nama_universitas[:35])
    c.setFillColorRGB(0, 0, 0)

    c.setFont('Helvetica-Bold', 5)
    c.drawString(73, 95, 'Nama')
    c.line(73, 93, 232, 93)
    c.setFont('Helvetica-Bold', 8.3)
    c.drawString(73, 84, pendaftar.nama.upper()[:28])

    c.setFont('Helvetica-Bold', 5)
    c.drawString(73, 75, 'Nomor Induk Mahasiswa / NIM')
    c.line(73, 73, 232, 73)
    c.setFont('Helvetica-Bold', 8.3)
    c.drawString(73, 64, reg.nim)

    c.setFont('Helvetica-Bold', 5)
    c.drawString(73, 57, 'Program Studi')
    c.line(73, 55, 232, 55)
    c.setFont('Helvetica-Bold', 8.3)
    c.drawString(73, 46, (prodi.nama.upper() if prodi else '-')[:32])

    c.setFont('Helvetica-Bold', 5)
    c.drawString(73, 38, 'Tahun Masuk')
    c.line(73, 36, 105, 36)
    c.setFont('Helvetica-Bold', 8.3)
    c.drawString(73, 27, site.tahun_pmb)

    ttl = pendaftar.tempat_lahir or ''
    if pendaftar.tanggal_lahir:
        ttl += ', ' + pendaftar.tanggal_lahir.strftime('%d %B %Y')
    qr_data = reg.nim + ';\n' + pendaftar.nama + ';\n' + ttl
    qrw = QrCodeWidget(qr_data)
    b   = qrw.getBounds()
    qw, qh = b[2] - b[0], b[3] - b[1]
    d = Drawing(48, 48, transform=[48. / qw, 0, 0, 48. / qh, 0, 0])
    d.add(qrw)
    renderPDF.draw(d, c, 160, 1)

    if os.path.isfile(foto_path):
        c.drawImage(foto_path, 7, 24, width=60, height=75)

    c.showPage()
    c.save()
    pdf = buffer.getvalue()
    buffer.close()
    response.write(pdf)
    return response


# ── Cleanup Data Duplikat & Hapus Manual (superuser only) ───────────────────

def _pendaftar_summary(p):
    """Ringkasan data per pendaftar untuk UI cleanup."""
    is_lulus = False
    try:
        if p.jalur == 'raport' and hasattr(p, 'raport_berkas'):
            is_lulus = p.raport_berkas.status == 3
        elif p.jalur == 'beasiswa' and hasattr(p, 'beasiswa_daftar'):
            is_lulus = p.beasiswa_daftar.status_seleksi == 3
        elif p.jalur == 'umum' and hasattr(p, 'umum_daftar'):
            is_lulus = p.umum_daftar.status == 2
    except Exception:
        pass
    return {
        'pendaftar': p,
        'has_registrasi': hasattr(p, 'registrasi'),
        'has_sekolah': hasattr(p, 'sekolah'),
        'ortu_count': p.ortu.count(),
        'is_lulus': is_lulus,
    }


def _delete_pendaftar_safe(request, p):
    """Hapus pendaftar + cascade (User→Pendaftar→semua relasi). Return True kalau sukses."""
    try:
        p.user.delete()
        return True
    except Exception as e:
        messages.warning(request, f'Gagal hapus {p.no_daftar} ({p.nama}): {e}')
        return False


@login_required
@superuser_required
def cleanup_duplikat(request):
    """Cleanup Pendaftar duplikat + hapus manual.

    Grouping duplikat bisa berdasarkan:
      - nik     : Pendaftar dengan NIK sama (default)
      - nama    : Pendaftar dengan nama sama (case-insensitive)
      - nama_tgl: Pendaftar dengan nama + tanggal_lahir sama (strongest)

    Akses dibatasi superuser. Cascade delete via User.delete() membersihkan
    Pendaftar + Sekolah + Ortu + jalur record + Registrasi sekaligus.
    """
    from django.db.models import Count, Q
    from django.db.models.functions import Lower

    group_by = request.GET.get('group_by', 'nik')
    if group_by not in ('nik', 'nama', 'nama_tgl'):
        group_by = 'nik'
    search_query = request.GET.get('q', '').strip()

    # ── POST handlers ────────────────────────────────────────────────────────
    if request.method == 'POST':
        action = request.POST.get('action', '')

        if action == 'cleanup_groups':
            deleted_count = 0
            skipped_groups = 0

            for key, keep_id in request.POST.items():
                if not key.startswith('keep_'):
                    continue
                if not keep_id:
                    skipped_groups += 1
                    continue
                try:
                    keep_id = int(keep_id)
                except (ValueError, TypeError):
                    skipped_groups += 1
                    continue

                group_key = key[len('keep_'):]
                # Resolve to_delete queryset berdasarkan group_by
                if group_by == 'nik':
                    to_delete = Pendaftar.objects.filter(NIK=group_key).exclude(pk=keep_id)
                elif group_by == 'nama':
                    to_delete = (Pendaftar.objects.annotate(_nm=Lower('nama'))
                                 .filter(_nm=group_key).exclude(pk=keep_id))
                else:  # nama_tgl
                    parts = group_key.rsplit('|', 1)
                    nama_l = parts[0]
                    tgl = parts[1] if len(parts) > 1 and parts[1] else None
                    qs = Pendaftar.objects.annotate(_nm=Lower('nama')).filter(_nm=nama_l)
                    qs = qs.filter(tanggal_lahir=tgl) if tgl else qs.filter(tanggal_lahir__isnull=True)
                    to_delete = qs.exclude(pk=keep_id)

                for p in to_delete:
                    if _delete_pendaftar_safe(request, p):
                        deleted_count += 1

            if deleted_count:
                messages.success(request, f'Berhasil menghapus {deleted_count} pendaftar duplikat.')
            if skipped_groups:
                messages.info(request, f'{skipped_groups} grup di-skip (belum pilih keep).')
            return redirect(f'{request.path}?group_by={group_by}')

        elif action == 'manual_delete':
            ids = request.POST.getlist('delete_ids')
            deleted_count = 0
            for pid in ids:
                try:
                    p = Pendaftar.objects.get(pk=int(pid))
                    if _delete_pendaftar_safe(request, p):
                        deleted_count += 1
                except (Pendaftar.DoesNotExist, ValueError, TypeError):
                    continue
            if deleted_count:
                messages.success(request, f'Berhasil menghapus {deleted_count} pendaftar.')
            else:
                messages.info(request, 'Tidak ada pendaftar yang dihapus.')
            q = request.POST.get('q', '').strip()
            return redirect(f'{request.path}?group_by={group_by}' + (f'&q={q}' if q else ''))

    # ── Build groups untuk rendering ─────────────────────────────────────────
    groups = []

    if group_by == 'nik':
        dup_keys = list(
            Pendaftar.objects.exclude(NIK='')
            .values('NIK').annotate(n=Count('id')).filter(n__gt=1)
            .values_list('NIK', flat=True)
        )
        for nik in dup_keys:
            pendaftars = (Pendaftar.objects.filter(NIK=nik)
                          .select_related('user').order_by('created_at'))
            groups.append({
                'key': nik,
                'label': f'NIK: {nik}',
                'rows': [_pendaftar_summary(p) for p in pendaftars],
            })

    elif group_by == 'nama':
        dup_keys = list(
            Pendaftar.objects.annotate(_nm=Lower('nama'))
            .values('_nm').annotate(n=Count('id'))
            .filter(n__gt=1).exclude(_nm='')
            .values_list('_nm', flat=True)
        )
        for nm in dup_keys:
            pendaftars = (Pendaftar.objects.annotate(_nm2=Lower('nama'))
                          .filter(_nm2=nm).select_related('user').order_by('created_at'))
            first_nama = pendaftars.first().nama if pendaftars.exists() else nm
            groups.append({
                'key': nm,
                'label': f'Nama: {first_nama}',
                'rows': [_pendaftar_summary(p) for p in pendaftars],
            })

    else:  # nama_tgl
        dup_keys = list(
            Pendaftar.objects.annotate(_nm=Lower('nama'))
            .values('_nm', 'tanggal_lahir').annotate(n=Count('id'))
            .filter(n__gt=1).exclude(_nm='')
        )
        for item in dup_keys:
            nm = item['_nm']
            tgl = item['tanggal_lahir']
            qs = Pendaftar.objects.annotate(_nm2=Lower('nama')).filter(_nm2=nm)
            qs = qs.filter(tanggal_lahir=tgl) if tgl else qs.filter(tanggal_lahir__isnull=True)
            pendaftars = qs.select_related('user').order_by('created_at')
            first_nama = pendaftars.first().nama if pendaftars.exists() else nm
            key = f'{nm}|{tgl.isoformat() if tgl else ""}'
            label_tgl = tgl.strftime('%d/%m/%Y') if tgl else 'tanpa tgl lahir'
            groups.append({
                'key': key,
                'label': f'{first_nama} ({label_tgl})',
                'rows': [_pendaftar_summary(p) for p in pendaftars],
            })

    # ── Search manual delete ─────────────────────────────────────────────────
    search_results = []
    if search_query and len(search_query) >= 3:
        qs = (Pendaftar.objects
              .filter(
                  Q(nama__icontains=search_query) |
                  Q(user__email__icontains=search_query) |
                  Q(no_daftar__icontains=search_query) |
                  Q(NIK__icontains=search_query)
              )
              .select_related('user')
              .order_by('nama')[:100])
        search_results = [_pendaftar_summary(p) for p in qs]

    return render(request, 'panel/cleanup_duplikat.html', {
        'groups': groups,
        'total_groups': len(groups),
        'total_duplicates': sum(len(g['rows']) for g in groups),
        'group_by': group_by,
        'search_query': search_query,
        'search_results': search_results,
    })


# ── Cari Cama (Pencarian & Detail Calon Mahasiswa) ───────────────────────────

JALUR_MODEL_MAP = {
    'raport': (RaportBerkas, 'raport_berkas'),
    'beasiswa': (BeasiswaDaftar, 'beasiswa_daftar'),
    'umum': (UmumDaftar, 'umum_daftar'),
}


@login_required
@staff_required
def cari_cama(request):
    q = request.GET.get('q', '').strip()
    jalur_filter = request.GET.get('jalur', '')

    qs = Pendaftar.objects.select_related(
        'user', 'prodi1', 'prodi2'
    ).order_by('-created_at')

    if q:
        qs = qs.filter(
            Q(nama__icontains=q) |
            Q(no_daftar__icontains=q) |
            Q(NIK__icontains=q) |
            Q(no_hp__icontains=q) |
            Q(user__email__icontains=q)
        )
    if jalur_filter in {'raport', 'beasiswa', 'umum'}:
        qs = qs.filter(jalur=jalur_filter)

    qs = qs[:200]
    return render(request, 'panel/cari_cama.html', {
        'object_list': qs,
        'q': q,
        'jalur_filter': jalur_filter,
        'jalur_choices': [('raport', 'Jalur Raport'), ('beasiswa', 'Jalur Beasiswa'), ('umum', 'Jalur Umum')],
    })


@login_required
@staff_required
def cari_cama_detail(request, pk):
    pendaftar = get_object_or_404(
        Pendaftar.objects.select_related('user', 'prodi1', 'prodi2'), pk=pk
    )

    if request.method == 'POST':
        action = request.POST.get('action', '')
        if action == 'ganti_jalur':
            jalur_baru = request.POST.get('jalur_baru', '').strip()
            if jalur_baru not in JALUR_MODEL_MAP:
                messages.error(request, 'Jalur tujuan tidak valid.')
            elif jalur_baru == pendaftar.jalur:
                messages.info(request, f'Cama sudah berada di jalur {pendaftar.get_jalur_display()}.')
            else:
                # Hapus record jalur lama (raport_berkas / beasiswa_daftar / umum_daftar)
                # supaya tidak nyangkut. Kelulusan dan registrasi otomatis ikut dibatalkan.
                old_label = pendaftar.get_jalur_display()
                old_model, old_attr = JALUR_MODEL_MAP[pendaftar.jalur]
                old_obj = old_model.objects.filter(pendaftar=pendaftar).first()
                if old_obj:
                    old_obj.delete()

                # Reset Registrasi (kalau ada) supaya tidak menyimpan prodi_lulus dari jalur lama
                reg = Registrasi.objects.filter(pendaftar=pendaftar).first()
                if reg:
                    reg.delete()

                pendaftar.jalur = jalur_baru
                pendaftar.save(update_fields=['jalur'])

                messages.success(
                    request,
                    f'Jalur {pendaftar.nama} berhasil diubah dari {old_label} '
                    f'ke {pendaftar.get_jalur_display()}. Data jalur lama (kelulusan/registrasi) '
                    f'sudah direset; cama wajib mengulang proses jalur baru.'
                )
            return redirect('panel:cari_cama_detail', pk=pendaftar.pk)

    # Build context: alamat, sekolah, ortu, jalur record, registrasi
    alamat   = getattr(pendaftar, 'alamat', None)
    sekolah  = getattr(pendaftar, 'sekolah', None)
    ortu_ayah = pendaftar.ortu.filter(hubungan='ayah').first()
    ortu_ibu  = pendaftar.ortu.filter(hubungan='ibu').first()

    jalur_obj = None
    jalur_label = ''
    jalur_status = ''
    try:
        if pendaftar.jalur == 'raport' and hasattr(pendaftar, 'raport_berkas'):
            jalur_obj = pendaftar.raport_berkas
            jalur_label = 'Berkas Raport'
            jalur_status = jalur_obj.get_status_display()
        elif pendaftar.jalur == 'beasiswa' and hasattr(pendaftar, 'beasiswa_daftar'):
            jalur_obj = pendaftar.beasiswa_daftar
            jalur_label = 'Pendaftaran Beasiswa'
            jalur_status = jalur_obj.get_status_seleksi_display()
        elif pendaftar.jalur == 'umum' and hasattr(pendaftar, 'umum_daftar'):
            jalur_obj = pendaftar.umum_daftar
            jalur_label = 'Pendaftaran Jalur Umum'
            jalur_status = jalur_obj.get_status_display()
    except Exception:
        pass

    registrasi = Registrasi.objects.filter(pendaftar=pendaftar).first()

    return render(request, 'panel/cari_cama_detail.html', {
        'pendaftar':    pendaftar,
        'alamat':       alamat,
        'sekolah':      sekolah,
        'ortu_ayah':    ortu_ayah,
        'ortu_ibu':     ortu_ibu,
        'jalur_obj':    jalur_obj,
        'jalur_label':  jalur_label,
        'jalur_status': jalur_status,
        'registrasi':   registrasi,
        'jalur_choices': [
            ('raport', 'Jalur Raport'),
            ('beasiswa', 'Jalur Beasiswa'),
            ('umum', 'Jalur Umum'),
        ],
    })


@require_POST
@login_required
@staff_required
def cari_cama_reset_password(request, pk):
    """Reset password akun cama (Pendaftar) dari halaman detail.

    Operator mengetik password baru langsung di modal; password wajib
    diisi minimal 6 karakter.
    """
    pendaftar = get_object_or_404(Pendaftar.objects.select_related('user'), pk=pk)
    user = pendaftar.user
    if user is None:
        messages.error(request, 'Cama ini belum memiliki akun login.')
        return redirect('panel:cari_cama_detail', pk=pendaftar.pk)

    password = request.POST.get('password', '').strip()
    if not password:
        messages.error(request, 'Password baru wajib diisi.')
        return redirect('panel:cari_cama_detail', pk=pendaftar.pk)
    if len(password) < 6:
        messages.error(request, 'Password minimal 6 karakter.')
        return redirect('panel:cari_cama_detail', pk=pendaftar.pk)

    user.set_password(password)
    user.save(update_fields=['password'])
    messages.success(
        request,
        f'Password {pendaftar.nama} berhasil direset. '
        f'Mohon segera diinformasikan ke cama.'
    )
    return redirect('panel:cari_cama_detail', pk=pendaftar.pk)


# ── Master Jenis Beasiswa ─────────────────────────────────────────────────────

@login_required
@staff_required
def jenis_beasiswa_list(request):
    q = request.GET.get('q', '').strip()
    qs = JenisBeasiswa.objects.all().order_by('urutan', 'nama')
    if q:
        qs = qs.filter(Q(kode__icontains=q) | Q(nama__icontains=q))
    return render(request, 'panel/jenis_beasiswa_list.html', {
        'object_list': qs,
        'q': q,
    })


@login_required
@staff_required
def jenis_beasiswa_form(request, pk=None):
    obj = get_object_or_404(JenisBeasiswa, pk=pk) if pk else None
    if request.method == 'POST':
        form = JenisBeasiswaForm(request.POST, instance=obj)
        if form.is_valid():
            form.save()
            messages.success(
                request,
                f'Jenis beasiswa "{form.cleaned_data["nama"]}" berhasil disimpan.'
            )
            return redirect('panel:jenis_beasiswa_list')
    else:
        form = JenisBeasiswaForm(instance=obj)
    return render(request, 'panel/jenis_beasiswa_form.html', {
        'form': form,
        'obj': obj,
        'is_edit': obj is not None,
    })


@login_required
@staff_required
def jenis_beasiswa_delete(request, pk):
    obj = get_object_or_404(JenisBeasiswa, pk=pk)
    if request.method == 'POST':
        # Cegah hapus kalau masih dipakai pendaftar
        in_use = BeasiswaDaftar.objects.filter(jenis_beasiswa=obj.kode).count()
        if in_use:
            messages.error(
                request,
                f'Tidak bisa menghapus "{obj.nama}" — masih dipakai oleh {in_use} pendaftar. '
                f'Nonaktifkan saja agar tidak muncul di form cama.'
            )
            return redirect('panel:jenis_beasiswa_list')
        nama = obj.nama
        obj.delete()
        messages.success(request, f'Jenis beasiswa "{nama}" dihapus.')
        return redirect('panel:jenis_beasiswa_list')
    return redirect('panel:jenis_beasiswa_list')


# ── Pengaturan Situs ──────────────────────────────────────────────────────────

@login_required
@staff_required
def pengaturan(request):
    obj = SiteSetting.get_instance()
    if request.method == 'POST':
        form = SiteSettingForm(request.POST, request.FILES, instance=obj)
        if form.is_valid():
            form.save()
            messages.success(request, 'Pengaturan situs berhasil disimpan.')
            return redirect('panel:pengaturan')
        else:
            messages.error(request, 'Periksa kembali isian formulir.')
    else:
        form = SiteSettingForm(instance=obj)
    return render(request, 'panel/pengaturan.html', {'form': form, 'obj': obj})


# ── Integrasi API (koneksi ke Sistem Keuangan) ────────────────────────────────

@login_required
@superuser_required
def integrasi_config(request):
    """Kelola konfigurasi integrasi API (koneksi keluar ke Sistem Keuangan)."""
    obj = IntegrasiConfig.load()
    test_result = None

    if request.method == 'POST':
        action = request.POST.get('action', 'save')
        if action == 'test':
            test_result = _test_koneksi_keuangan(obj)
            form = IntegrasiConfigForm(instance=obj)
        else:
            form = IntegrasiConfigForm(request.POST, instance=obj)
            if form.is_valid():
                form.save()
                messages.success(request, 'Konfigurasi integrasi API berhasil disimpan.')
                return redirect('panel:integrasi_config')
            messages.error(request, 'Periksa kembali isian formulir.')
    else:
        form = IntegrasiConfigForm(instance=obj)

    return render(request, 'panel/integrasi_config.html', {
        'form': form, 'obj': obj, 'test_result': test_result,
    })


def _test_koneksi_keuangan(cfg):
    """Best-effort cek konektivitas ke base URL Sistem Keuangan."""
    import urllib.request
    import urllib.error
    if not cfg.keuangan_api_base:
        return {'ok': False, 'detail': 'Base URL Sistem Keuangan belum diisi.'}
    url = cfg.keuangan_api_base.rstrip('/') + '/'
    try:
        req = urllib.request.Request(url, method='GET')
        with urllib.request.urlopen(req, timeout=cfg.keuangan_timeout or 10) as resp:
            return {'ok': True, 'detail': f'Server merespons (HTTP {resp.status}).'}
    except urllib.error.HTTPError as exc:
        # Server menjawab (mis. 404/403) → host terjangkau.
        return {'ok': True, 'detail': f'Server terjangkau (HTTP {exc.code}).'}
    except Exception as exc:  # noqa: BLE001
        return {'ok': False, 'detail': f'Tidak dapat terhubung: {exc}'}


# ── Import Pendaftar dari CSV ──────────────────────────────────────────────────

@login_required
@staff_required
def import_pendaftar_template(request):
    """Unduh template CSV (header + 1 baris contoh)."""
    from django.http import HttpResponse
    from pendaftaran.importer import build_template_csv
    resp = HttpResponse(build_template_csv(), content_type='text/csv; charset=utf-8')
    resp['Content-Disposition'] = 'attachment; filename="template_pendaftar.csv"'
    return resp


@login_required
@staff_required
def import_pendaftar(request):
    """Upload CSV pendaftar → import (atau preview dengan dry-run)."""
    import io
    from pendaftaran.importer import import_csv_file, TEMPLATE_HEADER

    report = None
    if request.method == 'POST':
        f = request.FILES.get('csv_file')
        if not f:
            messages.error(request, 'Pilih file CSV terlebih dahulu.')
        else:
            opts = dict(
                dry_run=request.POST.get('dry_run') == '1',
                allow_empty_nik=request.POST.get('allow_empty_nik') == '1',
                force_refresh=request.POST.get('force_refresh') == '1',
                default_jalur=request.POST.get('default_jalur', 'raport'),
            )
            try:
                text = io.TextIOWrapper(f.file, encoding='utf-8-sig', newline='')
                report = import_csv_file(text, **opts)
                if report.dry_run:
                    messages.info(
                        request,
                        f'[PREVIEW] {report.imported} siap diimport, '
                        f'{report.skipped_existing} sudah ada, '
                        f'{report.skipped_invalid} tidak valid, {report.errored} error. '
                        'Hilangkan centang "Preview" untuk menyimpan.'
                    )
                else:
                    messages.success(
                        request,
                        f'Import selesai: {report.imported} ditambahkan, '
                        f'{report.skipped_existing} sudah ada, '
                        f'{report.skipped_invalid} tidak valid, {report.errored} error.'
                    )
            except ValueError as e:
                messages.error(request, f'Gagal membaca CSV: {e}')
            except Exception as e:
                messages.error(request, f'Terjadi kesalahan: {e}')

    return render(request, 'panel/import_pendaftar.html', {
        'report': report,
        'kolom': TEMPLATE_HEADER,
    })


# ── Kelola User (akun staff/admin) ────────────────────────────────────────────
#
# Hanya superuser yang boleh CRUD akun. Daftar dibatasi akun yang TIDAK punya
# Pendaftar terkait — supaya tidak tercampur dengan ribuan akun cama. Ini
# konsisten dengan tujuan menu: mengelola akun panitia/admin saja.

def _is_admin_account(user):
    """User dianggap akun admin kalau bukan akun cama (tidak punya Pendaftar)."""
    return not Pendaftar.objects.filter(user=user).exists()


@login_required
@superuser_required
def kelola_user_list(request):
    q     = request.GET.get('q', '').strip()
    role  = request.GET.get('role', '')  # '', 'super', 'staff'

    # Exclude akun cama (yang punya Pendaftar)
    cama_user_ids = Pendaftar.objects.values_list('user_id', flat=True)
    qs = User.objects.exclude(pk__in=cama_user_ids).order_by('username')

    if q:
        qs = qs.filter(
            Q(username__icontains=q) |
            Q(email__icontains=q) |
            Q(first_name__icontains=q) |
            Q(last_name__icontains=q)
        )
    if role == 'super':
        qs = qs.filter(is_superuser=True)
    elif role == 'staff':
        qs = qs.filter(is_staff=True, is_superuser=False)

    return render(request, 'panel/kelola_user_list.html', {
        'object_list': qs,
        'q':           q,
        'role':        role,
    })


@login_required
@superuser_required
def kelola_user_form(request, pk=None):
    obj = None
    if pk:
        obj = get_object_or_404(User, pk=pk)
        if not _is_admin_account(obj):
            messages.error(request, 'Akun ini terdaftar sebagai pendaftar/cama dan tidak dikelola di menu ini.')
            return redirect('panel:kelola_user_list')

    if request.method == 'POST':
        username   = request.POST.get('username', '').strip()
        email      = request.POST.get('email', '').strip()
        first_name = request.POST.get('first_name', '').strip()
        last_name  = request.POST.get('last_name', '').strip()
        password   = request.POST.get('password', '')
        is_staff_v     = request.POST.get('is_staff') == '1'
        is_superuser_v = request.POST.get('is_superuser') == '1'
        is_active_v    = request.POST.get('is_active') == '1'

        errors = []
        if not username:
            errors.append('Username wajib diisi.')
        else:
            dup = User.objects.filter(username=username)
            if obj:
                dup = dup.exclude(pk=obj.pk)
            if dup.exists():
                errors.append(f'Username "{username}" sudah dipakai.')

        if obj is None and not password:
            errors.append('Password wajib diisi saat membuat akun baru.')
        if password and len(password) < 6:
            errors.append('Password minimal 6 karakter.')

        # Jangan biarkan admin meng-non-aktifkan / mencabut hak superuser dirinya sendiri
        if obj and obj.pk == request.user.pk:
            if not is_superuser_v:
                errors.append('Anda tidak bisa mencabut status superuser dari akun sendiri.')
            if not is_active_v:
                errors.append('Anda tidak bisa menonaktifkan akun sendiri.')

        if errors:
            for e in errors:
                messages.error(request, e)
            return render(request, 'panel/kelola_user_form.html', {
                'obj':     obj,
                'is_edit': obj is not None,
                'form_data': {
                    'username': username, 'email': email,
                    'first_name': first_name, 'last_name': last_name,
                    'is_staff': is_staff_v, 'is_superuser': is_superuser_v,
                    'is_active': is_active_v,
                },
            })

        if obj is None:
            obj = User(username=username)
        else:
            obj.username = username
        obj.email      = email
        obj.first_name = first_name
        obj.last_name  = last_name
        obj.is_staff      = is_staff_v or is_superuser_v  # superuser otomatis staff
        obj.is_superuser  = is_superuser_v
        obj.is_active     = is_active_v
        if password:
            obj.set_password(password)
        obj.save()
        messages.success(request, f'Akun "{obj.username}" berhasil disimpan.')
        return redirect('panel:kelola_user_list')

    # GET
    if obj:
        form_data = {
            'username':     obj.username,
            'email':        obj.email,
            'first_name':   obj.first_name,
            'last_name':    obj.last_name,
            'is_staff':     obj.is_staff,
            'is_superuser': obj.is_superuser,
            'is_active':    obj.is_active,
        }
    else:
        form_data = {
            'username': '', 'email': '', 'first_name': '', 'last_name': '',
            'is_staff': True, 'is_superuser': False, 'is_active': True,
        }
    return render(request, 'panel/kelola_user_form.html', {
        'obj':       obj,
        'is_edit':   obj is not None,
        'form_data': form_data,
    })


@require_POST
@login_required
@superuser_required
def kelola_user_delete(request, pk):
    obj = get_object_or_404(User, pk=pk)
    if obj.pk == request.user.pk:
        messages.error(request, 'Anda tidak bisa menghapus akun sendiri.')
        return redirect('panel:kelola_user_list')
    if not _is_admin_account(obj):
        messages.error(request, 'Akun pendaftar/cama tidak boleh dihapus dari menu ini. Gunakan Cleanup Duplikat.')
        return redirect('panel:kelola_user_list')
    username = obj.username
    obj.delete()
    messages.success(request, f'Akun "{username}" dihapus.')
    return redirect('panel:kelola_user_list')


@require_POST
@login_required
@superuser_required
def kelola_user_reset_password(request, pk):
    obj = get_object_or_404(User, pk=pk)
    if not _is_admin_account(obj):
        messages.error(request, 'Akun ini tidak dikelola di menu ini.')
        return redirect('panel:kelola_user_list')
    password = request.POST.get('password', '')
    if len(password) < 6:
        messages.error(request, 'Password minimal 6 karakter.')
        return redirect('panel:kelola_user_list')
    obj.set_password(password)
    obj.save(update_fields=['password'])
    messages.success(request, f'Password "{obj.username}" berhasil direset.')
    return redirect('panel:kelola_user_list')


# ── Kelola Prodi (Kuota) ──────────────────────────────────────────────────────

@login_required
@staff_required
def kelola_prodi_list(request):
    from django.db.models import Count

    q          = request.GET.get('q', '').strip()
    fakultas_f = request.GET.get('fakultas', '').strip()
    jenjang_f  = request.GET.get('jenjang', '').strip()
    show_only  = request.GET.get('show', '').strip()  # 'unset' | 'over'

    qs = Prodi.objects.all().order_by('fakultas', 'nama')
    if q:
        qs = qs.filter(Q(nama__icontains=q) | Q(kode__icontains=q) | Q(FID__icontains=q))
    if fakultas_f:
        qs = qs.filter(fakultas=fakultas_f)
    if jenjang_f:
        qs = qs.filter(jenjang=jenjang_f)

    if request.method == 'POST':
        updated = 0
        errors  = []
        for p in qs:
            raw = request.POST.get(f'kuota_{p.pk}')
            if raw is None:
                continue
            raw = raw.strip()
            try:
                nilai = int(raw) if raw != '' else 0
            except ValueError:
                errors.append(f'{p.nama}: nilai "{raw}" bukan angka')
                continue
            if nilai < 0:
                errors.append(f'{p.nama}: kuota tidak boleh negatif')
                continue
            if nilai != p.kuota:
                p.kuota = nilai
                p.save(update_fields=['kuota'])
                updated += 1
        if updated:
            messages.success(request, f'{updated} prodi diperbarui.')
        if errors:
            messages.warning(request, 'Beberapa baris dilewati: ' + '; '.join(errors[:8])
                             + (' ...' if len(errors) > 8 else ''))
        if not updated and not errors:
            messages.info(request, 'Tidak ada perubahan.')
        # Pertahankan filter setelah submit
        return redirect(f"{request.path}?{request.GET.urlencode()}")

    # Hitung pendaftar per prodi (sekali query) untuk tampilan
    counts = {r['prodi1']: r['c'] for r in
              Pendaftar.objects.values('prodi1').annotate(c=Count('id'))}

    rows = []
    for p in qs:
        jumlah = counts.get(p.pk, 0)
        kuota  = p.kuota or 0
        sisa   = (kuota - jumlah) if kuota else None
        pct    = round(jumlah / kuota * 100) if kuota else 0
        rows.append({
            'obj':    p,
            'jumlah': jumlah,
            'kuota':  kuota,
            'sisa':   sisa if sisa is None or sisa >= 0 else 0,
            'pct':    min(pct, 999),
            'lebih':  (jumlah - kuota) if (kuota and jumlah > kuota) else 0,
            'over':   bool(kuota and jumlah > kuota),
            'unset':  kuota == 0,
        })

    if show_only == 'unset':
        rows = [r for r in rows if r['unset']]
    elif show_only == 'over':
        rows = [r for r in rows if r['over']]

    fakultas_list = list(Prodi.objects.order_by('fakultas')
                         .values_list('fakultas', flat=True).distinct())
    jenjang_list  = [k for k, _ in Prodi.JENJANG]

    summary = {
        'jml_prodi':   Prodi.objects.count(),
        'jml_aktif':   Prodi.objects.filter(aktif=True).count(),
        'jml_unset':   Prodi.objects.filter(aktif=True, kuota=0).count(),
        'total_kuota': sum((p.kuota or 0) for p in Prodi.objects.filter(aktif=True)),
        'total_daftar': Pendaftar.objects.count(),
    }

    return render(request, 'panel/kelola_prodi_list.html', {
        'rows':           rows,
        'q':              q,
        'fakultas_f':     fakultas_f,
        'jenjang_f':      jenjang_f,
        'show_only':      show_only,
        'fakultas_list':  fakultas_list,
        'jenjang_list':   jenjang_list,
        'summary':        summary,
    })
