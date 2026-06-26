import logging

from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth import authenticate, login, logout
from django.contrib.auth.decorators import login_required
from django.contrib.auth.models import User
from django.contrib import messages
from django.contrib.auth import update_session_auth_hash
from django.utils.http import url_has_allowed_host_and_scheme
from .models import Pendaftar, Alamat, Sekolah, Ortu, Registrasi, generate_no_daftar, JALUR, KELAS_REGISTRASI
from .forms import DataDiriForm, AlamatForm, SekolahForm, OrtuForm, FormBerkasRegistrasi, BootstrapPasswordChangeForm
from core.models import Setting, Prodi, SiteSetting, BiayaKuliahPeriode, BiayaKuliahProdi, Provinsi, Kota

logger = logging.getLogger(__name__)


MAPEL_LIST = [
    'Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi',
    'Sejarah', 'Geografi', 'Ekonomi', 'Sosiologi', 'PKn', 'Agama', 'Seni Budaya',
    'Penjaskes', 'TIK / Informatika', 'Bahasa Asing Lain',
]


def _is_staff(user):
    return user.is_authenticated and (user.is_staff or user.is_superuser)


def home(request):
    if request.user.is_authenticated:
        # Staff/admin → panel (jangan ke dashboard CAMA, mencegah loop redirect).
        if _is_staff(request.user):
            return redirect('panel:dashboard')
        # Hanya pendaftar (CAMA) yang diarahkan ke dashboard.
        if Pendaftar.objects.filter(user=request.user).exists():
            return redirect('dashboard')
        # Authenticated tapi bukan staff & bukan CAMA → tampilkan home biasa.
    from datetime import date
    tahun = date.today().year
    jalur_status = {
        'raport': Setting.get('RAPORT_BUKA', '1'),
        'beasiswa': Setting.get('BEASISWA_BUKA', '1'),
        'umum': Setting.get('UMUM_BUKA', '1'),
    }
    return render(request, 'pendaftaran/home.html', {
        'jalur_status': jalur_status,
        'tahun': tahun,
        'tahun_ajaran': f'{tahun}/{tahun+1}',
    })


def register(request):
    if request.user.is_authenticated:
        return redirect('dashboard')
    jalur = request.GET.get('jalur', request.POST.get('jalur', ''))
    if jalur not in dict(JALUR):
        return redirect('home')

    prodi_list = Prodi.objects.filter(aktif=True).order_by('fakultas', 'nama')

    jenis_beasiswa_list = []
    if jalur == 'beasiswa':
        from beasiswa.models import JenisBeasiswa
        jenis_beasiswa_list = list(JenisBeasiswa.objects.filter(aktif=True).order_by('urutan', 'nama'))

    if request.method == 'POST':
        nama = request.POST.get('nama', '').strip()
        nik = request.POST.get('nik', '').strip()
        no_hp = request.POST.get('no_hp', '').strip()
        email = request.POST.get('email', '').strip()
        password = request.POST.get('password', '')
        password2 = request.POST.get('password2', '')
        prodi1_id = request.POST.get('prodi1')
        prodi2_id = request.POST.get('prodi2') or None
        jenis_beasiswa_kode = (request.POST.get('jenis_beasiswa', '') or '').strip()

        # Periode PMB aktif: berdasarkan SiteSetting.tahun_pmb (fallback tahun sekarang)
        site = SiteSetting.get_instance()
        try:
            tahun_pmb = int(str(site.tahun_pmb).strip()[:4])
        except (ValueError, TypeError, AttributeError):
            from django.utils import timezone
            tahun_pmb = timezone.now().year

        errors = []
        if not nama: errors.append('Nama wajib diisi.')
        if not nik or len(nik) != 16 or not nik.isdigit():
            errors.append('NIK harus 16 digit angka.')
        elif Pendaftar.objects.filter(NIK=nik, created_at__year=tahun_pmb).exists():
            errors.append(
                f'NIK {nik} sudah terdaftar di PMB periode {tahun_pmb}. '
                'Silakan login jika Anda sudah punya akun.'
            )
        if not no_hp: errors.append('No HP wajib diisi.')
        if not email: errors.append('Email wajib diisi.')
        if password != password2: errors.append('Konfirmasi password tidak cocok.')
        if len(password) < 8: errors.append('Password minimal 8 karakter.')
        if User.objects.filter(username=email).exists(): errors.append('Email sudah terdaftar.')
        if not prodi1_id: errors.append('Pilihan Prodi 1 wajib dipilih.')
        if jalur == 'beasiswa':
            kode_aktif = {j.kode for j in jenis_beasiswa_list}
            if not jenis_beasiswa_kode:
                errors.append('Jenis Beasiswa wajib dipilih.')
            elif jenis_beasiswa_kode not in kode_aktif:
                errors.append('Jenis Beasiswa yang dipilih tidak valid.')

        if errors:
            for e in errors:
                messages.error(request, e)
        else:
            user = User.objects.create_user(username=email, email=email, password=password, first_name=nama)
            prodi1 = get_object_or_404(Prodi, pk=prodi1_id)
            prodi2 = Prodi.objects.filter(pk=prodi2_id).first() if prodi2_id else None
            pendaftar = Pendaftar.objects.create(
                user=user,
                no_daftar=generate_no_daftar(jalur),
                nama=nama, NIK=nik, no_hp=no_hp,
                jalur=jalur, jenis_kelamin='L',
                tempat_lahir='', agama='Islam',
                prodi1=prodi1, prodi2=prodi2,
            )
            if jalur == 'beasiswa':
                from beasiswa.models import BeasiswaDaftar
                BeasiswaDaftar.objects.create(
                    pendaftar=pendaftar,
                    jenis_beasiswa=jenis_beasiswa_kode,
                    batch=1,
                )
            # Buat tagihan biaya pendaftaran di Sistem Keuangan (best-effort,
            # tidak menggagalkan registrasi bila keuangan tidak terjangkau).
            try:
                from core.keuangan_client import kirim_tagihan_pendaftaran
                kirim_tagihan_pendaftaran(pendaftar)
            except Exception:
                logger.exception('Gagal push tagihan pendaftaran %s', pendaftar.no_daftar)
            login(request, user)
            messages.success(request, f'Akun berhasil dibuat. Selamat datang, {nama}!')
            return redirect('dashboard')

    return render(request, 'pendaftaran/register.html', {
        'jalur': jalur, 'prodi_list': prodi_list,
        'jenis_beasiswa_list': jenis_beasiswa_list,
    })


def login_view(request):
    if request.user.is_authenticated:
        return redirect('dashboard')
    if request.method == 'POST':
        email = request.POST.get('email')
        password = request.POST.get('password')
        user = authenticate(request, username=email, password=password)
        if user:
            login(request, user)
            next_url = request.GET.get('next') or request.POST.get('next')
            if next_url and url_has_allowed_host_and_scheme(
                next_url,
                allowed_hosts={request.get_host()},
                require_https=request.is_secure(),
            ):
                return redirect(next_url)
            return redirect('dashboard')
        messages.error(request, 'Email atau password salah.')
    return render(request, 'pendaftaran/login.html')


def logout_view(request):
    logout(request)
    return redirect('home')


@login_required
def dashboard(request):
    try:
        pendaftar = request.user.pendaftar
    except Pendaftar.DoesNotExist:
        # Staff yang nyasar ke dashboard CAMA → arahkan ke panel (anti-loop).
        if _is_staff(request.user):
            return redirect('panel:dashboard')
        return redirect('home')

    reg = Registrasi.objects.filter(pendaftar=pendaftar).first()
    ctx = {
        'pendaftar': pendaftar,
        'has_sekolah': Sekolah.objects.filter(pendaftar=pendaftar).exists(),
        'has_ortu': Ortu.objects.filter(pendaftar=pendaftar).exists(),
        'status_obj': None,
        'has_berkas': False,
        'has_nilai': False,
        'jumlah_nilai': 0,
        'has_registrasi': reg is not None,
        'has_registrasi_selesai': reg and reg.status == 1,
        'nim': reg.nim if reg else '',
        'nomor_va': reg.nomor_va if reg else '',
    }

    if pendaftar.jalur == 'raport':
        from raport.models import RaportBerkas, RaportNilai
        berkas = RaportBerkas.objects.filter(pendaftar=pendaftar).first()
        ctx['status_obj'] = berkas
        ctx['has_berkas'] = bool(berkas and berkas.file_raport)
        ctx['has_nilai'] = RaportNilai.objects.filter(pendaftar=pendaftar).exists()
        ctx['jumlah_nilai'] = RaportNilai.objects.filter(pendaftar=pendaftar).count()
    elif pendaftar.jalur == 'beasiswa':
        from beasiswa.models import BeasiswaDaftar
        bs = BeasiswaDaftar.objects.filter(pendaftar=pendaftar).first()
        ctx['status_obj'] = bs
        ctx['has_berkas'] = bool(bs and bs.file_formulir)
    elif pendaftar.jalur == 'umum':
        from umum.models import UmumDaftar
        ctx['status_obj'] = UmumDaftar.objects.filter(pendaftar=pendaftar).first()

    return render(request, 'pendaftaran/dashboard.html', ctx)


@login_required
def data_diri(request):
    pendaftar = request.user.pendaftar
    alamat = Alamat.objects.filter(pendaftar=pendaftar).first()

    if request.method == 'POST':
        form_diri = DataDiriForm(request.POST, request.FILES, instance=pendaftar)
        form_alamat = AlamatForm(request.POST, instance=alamat)
        if form_diri.is_valid() and form_alamat.is_valid():
            form_diri.save()
            a = form_alamat.save(commit=False)
            a.pendaftar = pendaftar
            a.save()
            messages.success(request, 'Data diri berhasil disimpan.')
            return redirect('dashboard')
    else:
        form_diri = DataDiriForm(instance=pendaftar)
        form_alamat = AlamatForm(instance=alamat)

    return render(request, 'pendaftaran/data_diri.html', {
        'form_diri': form_diri, 'form_alamat': form_alamat, 'pendaftar': pendaftar,
        'provinsi_list': Provinsi.objects.order_by('nama'),
        'kota_list': Kota.objects.order_by('nama'),
    })


@login_required
def upload_ktp(request):
    pendaftar = request.user.pendaftar
    if request.method == 'POST':
        if 'pas_foto' in request.FILES:
            pendaftar.pas_foto = request.FILES['pas_foto']
        if 'scan_ktp' in request.FILES:
            pendaftar.scan_ktp = request.FILES['scan_ktp']
        pendaftar.save()
        messages.success(request, 'File berhasil diupload.')
        return redirect('dashboard')
    return render(request, 'pendaftaran/upload_ktp.html', {'pendaftar': pendaftar})


@login_required
def data_sekolah(request):
    pendaftar = request.user.pendaftar
    sekolah = Sekolah.objects.filter(pendaftar=pendaftar).first()
    if request.method == 'POST':
        form = SekolahForm(request.POST, instance=sekolah)
        if form.is_valid():
            s = form.save(commit=False)
            s.pendaftar = pendaftar
            s.save()
            messages.success(request, 'Data sekolah berhasil disimpan.')
            return redirect('dashboard')
    else:
        form = SekolahForm(instance=sekolah)
    return render(request, 'pendaftaran/data_sekolah.html', {'form': form, 'pendaftar': pendaftar})


@login_required
def data_ortu(request):
    pendaftar = request.user.pendaftar
    ortu_ayah = Ortu.objects.filter(pendaftar=pendaftar, hubungan='ayah').first()
    ortu_ibu = Ortu.objects.filter(pendaftar=pendaftar, hubungan='ibu').first()

    if request.method == 'POST':
        form_ayah = OrtuForm(request.POST, instance=ortu_ayah, prefix='ayah')
        form_ibu = OrtuForm(request.POST, instance=ortu_ibu, prefix='ibu')
        if form_ayah.is_valid() and form_ibu.is_valid():
            a = form_ayah.save(commit=False)
            a.pendaftar = pendaftar
            a.hubungan = 'ayah'
            a.save()
            i = form_ibu.save(commit=False)
            i.pendaftar = pendaftar
            i.hubungan = 'ibu'
            i.save()
            messages.success(request, 'Data orang tua berhasil disimpan.')
            return redirect('dashboard')
    else:
        form_ayah = OrtuForm(instance=ortu_ayah, prefix='ayah')
        form_ibu = OrtuForm(instance=ortu_ibu, prefix='ibu')

    return render(request, 'pendaftaran/data_ortu.html', {
        'form_ayah': form_ayah, 'form_ibu': form_ibu, 'pendaftar': pendaftar,
    })


@login_required
def pantau_status(request):
    pendaftar = request.user.pendaftar
    ctx = {'pendaftar': pendaftar, 'status_obj': None}
    if pendaftar.jalur == 'raport':
        from raport.models import RaportBerkas
        ctx['status_obj'] = RaportBerkas.objects.filter(pendaftar=pendaftar).first()
    elif pendaftar.jalur == 'beasiswa':
        from beasiswa.models import BeasiswaDaftar
        ctx['status_obj'] = BeasiswaDaftar.objects.filter(pendaftar=pendaftar).first()
    elif pendaftar.jalur == 'umum':
        from umum.models import UmumDaftar
        ctx['status_obj'] = UmumDaftar.objects.filter(pendaftar=pendaftar).first()
    return render(request, 'pendaftaran/pantau_status.html', ctx)


def _get_prodi_lulus(pendaftar):
    """Ambil Prodi tempat cama dinyatakan lulus, sesuai jalur."""
    if pendaftar.jalur == 'raport':
        from raport.models import RaportBerkas
        rb = RaportBerkas.objects.filter(pendaftar=pendaftar, status=3).first()
        return rb.prodi_lulus if rb else None
    elif pendaftar.jalur == 'beasiswa':
        from beasiswa.models import BeasiswaDaftar
        bs = BeasiswaDaftar.objects.filter(pendaftar=pendaftar, status_seleksi=3).first()
        return bs.prodi_lulus if bs else None
    elif pendaftar.jalur == 'umum':
        from umum.models import UmumDaftar
        ud = UmumDaftar.objects.filter(pendaftar=pendaftar, status=2).first()
        return ud.prodi_lulus if ud else None
    return None


@login_required
def pilih_kelas(request):
    """Halaman cama pilih kelas setelah dinyatakan lulus.

    Menampilkan tabel biaya per kelas untuk prodi lulus, supaya cama bisa
    compare. Kelas yang tidak punya rincian biaya di-disable.
    """
    pendaftar = request.user.pendaftar
    reg = Registrasi.objects.filter(pendaftar=pendaftar).first()
    if not reg:
        messages.error(request, 'Anda belum dinyatakan lulus seleksi.')
        return redirect('pantau_status')

    prodi_lulus = _get_prodi_lulus(pendaftar)
    periode = BiayaKuliahPeriode.get_aktif()

    # Bangun mapping kelas → biaya untuk prodi ini
    biaya_per_kelas = {}
    if periode and prodi_lulus:
        for b in BiayaKuliahProdi.objects.filter(periode=periode, prodi=prodi_lulus):
            biaya_per_kelas[b.jenis_kelas] = b

    if request.method == 'POST':
        kelas = request.POST.get('kelas', '').strip()
        valid_keys = {k for k, _ in KELAS_REGISTRASI if k}
        if kelas not in valid_keys:
            messages.error(request, 'Kelas tidak valid.')
        elif kelas not in biaya_per_kelas and periode:
            messages.error(request, f'Kelas {kelas} belum tersedia untuk prodi {prodi_lulus}.')
        else:
            reg.kelas = kelas
            reg.save(update_fields=['kelas'])
            messages.success(request, f'Kelas berhasil dipilih: {reg.get_kelas_display()}.')
            # Kelas sudah pasti → buat tagihan DPP Cicilan 1 + SPP awal di keuangan
            # (nominal sesuai prodi × kelas). Best-effort, idempotent.
            try:
                from core.keuangan_client import kirim_tagihan_kelulusan
                hasil = kirim_tagihan_kelulusan(pendaftar)
                if hasil and hasil.get('nomor_va'):
                    messages.info(
                        request,
                        f"Tagihan DPP Cicilan 1 & SPP Cicilan 1 telah dibuat. "
                        f"No. Virtual Account Anda: {hasil['nomor_va']}",
                    )
                elif hasil and hasil.get('_error'):
                    messages.warning(
                        request,
                        f"Tagihan kuliah belum dapat dibuat di Sistem Keuangan: "
                        f"{hasil['detail']}",
                    )
            except Exception:
                logger.exception('Gagal push tagihan saat pilih kelas %s', pendaftar.no_daftar)
            return redirect('registrasi')

    # Pre-build list kelas siap-render (val, label, biaya, selected) supaya
    # template tidak perlu dict-lookup bertingkat.
    kelas_list = [
        {
            'val': val,
            'label': label,
            'biaya': biaya_per_kelas.get(val),
            'selected': reg.kelas == val,
        }
        for val, label in KELAS_REGISTRASI if val
    ]

    return render(request, 'pendaftaran/pilih_kelas.html', {
        'pendaftar': pendaftar,
        'reg': reg,
        'prodi_lulus': prodi_lulus,
        'periode': periode,
        'biaya_per_kelas': biaya_per_kelas,
        'kelas_list': kelas_list,
    })


@login_required
def registrasi(request):
    pendaftar = request.user.pendaftar

    # Hanya bisa diakses jika sudah dinyatakan lulus (ada record Registrasi)
    reg = Registrasi.objects.filter(pendaftar=pendaftar).first()
    if not reg:
        messages.error(request, 'Anda belum dinyatakan lulus seleksi.')
        return redirect('pantau_status')

    # Wajib pilih kelas dulu
    if not reg.kelas:
        messages.warning(request, 'Silakan pilih kelas terlebih dahulu sebelum upload berkas registrasi.')
        return redirect('pilih_kelas')

    # Catch-all: pastikan tagihan DPP/SPP sudah dibuat di keuangan (idempotent).
    # Menutup celah bila trigger saat Pilih Kelas terlewat/gagal sementara.
    if request.method == 'GET' and not reg.nomor_va:
        try:
            from core.keuangan_client import kirim_tagihan_kelulusan
            hasil = kirim_tagihan_kelulusan(pendaftar)
            if hasil and hasil.get('_error'):
                messages.warning(request, f'Tagihan kuliah belum dapat dibuat: {hasil["detail"]}')
        except Exception:
            logger.exception('Ensure tagihan registrasi %s', pendaftar.no_daftar)

    prodi_lulus = _get_prodi_lulus(pendaftar)

    form = FormBerkasRegistrasi(instance=reg)
    if request.method == 'POST':
        form = FormBerkasRegistrasi(request.POST, request.FILES, instance=reg)
        if form.is_valid():
            form.save()
            messages.success(request, 'Berkas registrasi berhasil diupload. Menunggu verifikasi admin untuk mendapatkan NIM.')
            return redirect('registrasi')
    return render(request, 'pendaftaran/registrasi.html', {
        'form': form,
        'reg': reg,
        'pendaftar': pendaftar,
        'prodi_lulus': prodi_lulus,
    })


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
def sertifikat_lulus_pdf(request):
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

    pendaftar = request.user.pendaftar
    prodi = tgl_diproses = nilai = None

    if pendaftar.jalur == 'raport':
        from raport.models import RaportBerkas
        rb = RaportBerkas.objects.filter(pendaftar=pendaftar, status=3).first()
        if rb:
            prodi, tgl_diproses, nilai = rb.prodi_lulus, rb.tgl_diproses, rb.nilai_raport
    elif pendaftar.jalur == 'beasiswa':
        from beasiswa.models import BeasiswaDaftar
        bs = BeasiswaDaftar.objects.filter(pendaftar=pendaftar, status_seleksi=3).first()
        if bs:
            prodi, tgl_diproses = bs.prodi_lulus, bs.tgl_diproses
    elif pendaftar.jalur == 'umum':
        from umum.models import UmumDaftar
        ud = UmumDaftar.objects.filter(pendaftar=pendaftar, status=2).first()
        if ud:
            prodi, tgl_diproses, nilai = ud.prodi_lulus, ud.tgl_diproses, ud.skor_cbt

    if not prodi:
        messages.error(request, 'Sertifikat hanya tersedia untuk pendaftar yang sudah dinyatakan Lulus.')
        return redirect('pantau_status')

    reg = Registrasi.objects.filter(pendaftar=pendaftar).first()
    if not reg or not reg.kelas:
        messages.warning(request, 'Pilih kelas terlebih dahulu sebelum mencetak sertifikat lulus.')
        return redirect('pilih_kelas')

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

        def _fmt(n):
            return 'Rp ' + f'{n:,.0f}'.replace(',', '.')

        y = 685
        c.setFont('Helvetica-Bold', 11)
        c.drawString(40, y, 'Biaya Umum'); y -= 4
        c.line(40, y, 555, y); y -= 14
        c.setFont('Courier', 11)
        c.drawString(50, y, 'Biaya Pendaftaran')
        c.drawRightString(540, y, _fmt(periode.biaya_pendaftaran)); y -= 16
        if periode.biaya_pkkmb:
            c.drawString(50, y, 'Biaya PKKMB')
            c.drawRightString(540, y, _fmt(periode.biaya_pkkmb)); y -= 16
        y -= 8

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
def cetak_bukti_reg(request):
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

    pendaftar = request.user.pendaftar
    reg = Registrasi.objects.filter(pendaftar=pendaftar, status=1).first()
    if not reg or not reg.nim:
        messages.error(request, 'NIM belum tersedia. Tunggu verifikasi admin.')
        return redirect('registrasi')

    prodi = reg.prodi_lulus
    site  = SiteSetting.get_instance()

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
def cetak_ktm(request):
    import os
    from io import BytesIO
    from django.http import HttpResponse
    from django.conf import settings as dj_settings
    from reportlab.pdfgen import canvas
    from reportlab.graphics.shapes import Drawing
    from reportlab.graphics.barcode.qr import QrCodeWidget
    from reportlab.graphics import renderPDF

    pendaftar = request.user.pendaftar
    reg = Registrasi.objects.filter(pendaftar=pendaftar, status=1).first()
    if not reg or not reg.nim:
        messages.error(request, 'NIM belum tersedia. Tunggu verifikasi admin.')
        return redirect('registrasi')

    prodi = reg.prodi_lulus
    site  = SiteSetting.get_instance()

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

    c.showPage()  # halaman 1: background KTM

    # halaman 2: data KTM depan
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


@login_required
def ganti_password(request):
    if request.method == 'POST':
        form = BootstrapPasswordChangeForm(request.user, request.POST)
        if form.is_valid():
            user = form.save()
            update_session_auth_hash(request, user)
            messages.success(request, 'Password berhasil diubah.')
            return redirect('dashboard')
    else:
        form = BootstrapPasswordChangeForm(request.user)
    return render(request, 'pendaftaran/ganti_password.html', {'form': form})
