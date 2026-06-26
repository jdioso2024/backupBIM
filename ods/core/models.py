from django.db import models
from django.core.exceptions import ValidationError
from .validators import (
    validate_image_ext, validate_image_size,
    validate_favicon_ext, validate_favicon_size,
)

_IMG_V = [validate_image_ext, validate_image_size]
_FAV_V = [validate_favicon_ext, validate_favicon_size]


class Provinsi(models.Model):
    nama = models.CharField(max_length=100)

    class Meta:
        db_table = 'provinsi'
        ordering = ['nama']

    def __str__(self):
        return self.nama


class Kota(models.Model):
    nama = models.CharField(max_length=100)
    provinsi = models.ForeignKey(Provinsi, on_delete=models.PROTECT)

    class Meta:
        db_table = 'kota'
        ordering = ['nama']

    def __str__(self):
        return self.nama


class Prodi(models.Model):
    JENJANG = (('S1', 'S1'), ('D3', 'D3'), ('D4', 'D4'), ('S2', 'S2'), ('S3', 'S3'))

    FID = models.CharField(max_length=20, unique=True)
    kode = models.CharField(max_length=20)
    nama = models.CharField(max_length=200)
    jenjang = models.CharField(max_length=5, choices=JENJANG)
    fakultas = models.CharField(max_length=200)
    aktif = models.BooleanField(default=True)
    kuota = models.PositiveIntegerField(default=0,
                                        help_text='Kuota pendaftar untuk PMB berjalan. 0 = belum diset.')

    class Meta:
        db_table = 'prodi'
        ordering = ['fakultas', 'nama']

    def __str__(self):
        return f'{self.nama} ({self.jenjang})'


class SiteSetting(models.Model):
    """Singleton — hanya satu record (pk=1). Berpengaruh ke dokumen (KTM, sertifikat, dll)."""

    # Identitas universitas
    nama_universitas = models.CharField(max_length=200, default='Universitas Muhammadiyah Surakarta')
    singkatan        = models.CharField(max_length=20,  default='UMS')
    alamat           = models.TextField(default='Jl. A. Yani Tromol Pos 1 Pabelan Kartasura')
    kota             = models.CharField(max_length=100, default='Surakarta')
    kode_pos         = models.CharField(max_length=10,  default='57102', blank=True)
    telepon          = models.CharField(max_length=50,  default='(0271) 717417', blank=True)
    website          = models.URLField(default='https://www.ums.ac.id', blank=True)
    email_pmb        = models.EmailField(default='pmb@ums.ac.id', blank=True)

    # Identitas program PMB
    nama_program     = models.CharField(max_length=200, default='PMB Terpadu')
    tahun_pmb        = models.CharField(max_length=10,  default='2026')
    tahun_ajaran     = models.CharField(max_length=20,  default='2026/2027')
    footer_copyright = models.CharField(max_length=200, default='DSTI UMS', blank=True)

    # Logo & favicon
    logo         = models.ImageField(upload_to='sitelogo/', null=True, blank=True,
                                     validators=_IMG_V,
                                     help_text='Logo utama (untuk KTM, sertifikat)')
    logo_kop     = models.ImageField(upload_to='sitelogo/', null=True, blank=True,
                                     validators=_IMG_V,
                                     help_text='Logo kop surat / dokumen resmi')
    logo_login   = models.ImageField(upload_to='sitelogo/', null=True, blank=True,
                                     validators=_IMG_V,
                                     help_text='Logo halaman login admin (versi gelap/terang sesuai tema)')
    favicon      = models.ImageField(upload_to='sitelogo/', null=True, blank=True,
                                     validators=_FAV_V,
                                     help_text='Favicon browser (.ico/.png)')

    # Pejabat penandatangan dokumen
    nama_pimpinan    = models.CharField(max_length=200, default='', blank=True,
                                        help_text='Nama rektor/pimpinan (untuk tanda tangan)')
    nip_pimpinan     = models.CharField(max_length=50,  default='', blank=True,
                                        help_text='NIP / NIK pimpinan')
    jabatan_pimpinan = models.CharField(max_length=100, default='Rektor', blank=True,
                                        help_text='Jabatan penandatangan')
    kota_ttd         = models.CharField(max_length=100, default='Surakarta', blank=True,
                                        help_text='Kota pada baris tanda tangan dokumen')

    class Meta:
        db_table  = 'site_setting'
        verbose_name        = 'Pengaturan Situs'
        verbose_name_plural = 'Pengaturan Situs'

    def __str__(self):
        return f'Pengaturan Situs — {self.nama_program} {self.tahun_ajaran}'

    def clean(self):
        if not self.pk and SiteSetting.objects.exists():
            raise ValidationError('Hanya boleh ada satu pengaturan situs.')

    def save(self, *args, **kwargs):
        self.pk = 1
        super().save(*args, **kwargs)

    def delete(self, *args, **kwargs):
        pass  # Singleton tidak bisa dihapus

    @classmethod
    def get_instance(cls):
        obj, _ = cls.objects.get_or_create(pk=1)
        return obj


class Setting(models.Model):
    key = models.CharField(max_length=100, unique=True)
    value = models.TextField()
    keterangan = models.TextField(blank=True)

    class Meta:
        db_table = 'setting'

    def __str__(self):
        return self.key

    @classmethod
    def get(cls, key, default=None):
        try:
            return cls.objects.get(key=key).value
        except cls.DoesNotExist:
            return default


class IntegrasiConfig(models.Model):
    """Konfigurasi integrasi API (singleton, pk=1).

    PMB Terpadu hanya MENGHUBUNGI Sistem Keuangan (push tagihan CAMA), jadi
    di sini hanya ada konfigurasi koneksi KELUAR. Nilai awal diambil dari
    environment (settings) saat record pertama dibuat; setelah itu DB menjadi
    sumber kebenaran sehingga bisa diubah dari menu tanpa redeploy.
    """

    # ── Keluar: Sistem Keuangan (POST /api/integrations/ods/tagihan/) ──
    keuangan_aktif = models.BooleanField(
        default=True,
        help_text='Aktifkan pengiriman tagihan ke Sistem Keuangan.')
    keuangan_api_base = models.URLField(
        max_length=300, blank=True,
        help_text='Base URL Sistem Keuangan, mis. https://keuangan.example.ac.id')
    keuangan_ods_token = models.CharField(
        max_length=200, blank=True,
        help_text='X-API-Key inbound keuangan (ODS_INBOUND_TOKEN di sisi keuangan).')
    keuangan_timeout = models.PositiveIntegerField(
        default=10, help_text='Timeout permintaan (detik).')

    diperbarui = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'integrasi_config'
        verbose_name = 'Konfigurasi Integrasi API'
        verbose_name_plural = 'Konfigurasi Integrasi API'

    def __str__(self):
        return 'Konfigurasi Integrasi API'

    def save(self, *args, **kwargs):
        self.pk = 1  # singleton
        super().save(*args, **kwargs)

    def delete(self, *args, **kwargs):
        pass  # singleton tidak bisa dihapus

    @classmethod
    def load(cls):
        """Ambil singleton; seed dari environment saat pertama kali dibuat."""
        obj = cls.objects.filter(pk=1).first()
        if obj is None:
            from django.conf import settings
            obj = cls(
                keuangan_api_base=getattr(settings, 'KEUANGAN_API_BASE', '') or '',
                keuangan_ods_token=getattr(settings, 'KEUANGAN_ODS_TOKEN', '') or '',
                keuangan_timeout=getattr(settings, 'KEUANGAN_TIMEOUT', 10) or 10,
            )
            obj.save()
        return obj


# ── Biaya Kuliah (per periode PMB) ────────────────────────────────────────────

KELAS_CHOICES = (
    ('reguler', 'Reguler'),
    ('karyawan', 'Karyawan'),
    ('internasional', 'Internasional'),
    ('boarding', 'International Boarding'),
)

KETERANGAN_DEFAULT = (
    '1. DPP (Dana Pengembangan dan Pembangunan) dapat dibayarkan 2 kali: '
    'pertama saat regristrasi 50%; kedua sebelum UAS 50%.\n'
    '2. SPP (Sumbangan Pembinaan dan Pendidikan) per semester dibayarkan '
    '2 kali, 50% saat awal semester dan 50% sebelum UAS.\n'
    '3. Biaya daftar ulang akan dikembalikan 75% apabila calon mahasiswa '
    'undur diri sebelum tanggal 8 Agustus 2026 karena diterima SNBP/SNBT, '
    'atau tidak lulus SLTA.\n'
    '4. Pembayaran regristrasi sampai sebelum 1 Juli 2026 mendapat potongan '
    '10% dari DPP; dan pembayaran tanggal 2 Juli–8 Agustus 2026 mendapat '
    'potongan 5% dari DPP.'
)


class BiayaKuliahPeriode(models.Model):
    """Header biaya kuliah per periode PMB (satu record per tahun).

    Biaya pendaftaran & PKKMB global (berlaku semua prodi). Rincian per prodi
    × kelas di model BiayaKuliahProdi (related_name='detail').
    """
    tahun_pmb = models.CharField(max_length=10, unique=True,
                                 help_text='Contoh: "2026" atau "2026/2027"')
    biaya_pendaftaran = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    biaya_pkkmb       = models.DecimalField(max_digits=12, decimal_places=2, default=0)
    keterangan        = models.TextField(blank=True, default=KETERANGAN_DEFAULT,
                                         help_text='Tampil di sertifikat lulus')
    aktif             = models.BooleanField(default=True,
                                            help_text='Periode yang dipakai untuk PMB saat ini')
    created_at        = models.DateTimeField(auto_now_add=True)
    updated_at        = models.DateTimeField(auto_now=True)

    class Meta:
        db_table = 'biaya_kuliah_periode'
        verbose_name = 'Periode Biaya Kuliah'
        verbose_name_plural = 'Periode Biaya Kuliah'
        ordering = ['-tahun_pmb']

    def __str__(self):
        return f'Biaya PMB {self.tahun_pmb}'

    @classmethod
    def get_aktif(cls):
        """Ambil periode aktif sesuai tahun_pmb di SiteSetting."""
        site = SiteSetting.get_instance()
        tahun = str(site.tahun_pmb).strip()
        return cls.objects.filter(tahun_pmb=tahun, aktif=True).first() \
            or cls.objects.filter(aktif=True).first()


class BiayaKuliahProdi(models.Model):
    """Rincian biaya per prodi × kelas untuk satu periode PMB.

    Unique per (periode, prodi, jenis_kelas). Prodi tanpa kelas tertentu
    (mis. Teknologi Pangan tanpa kelas internasional) cukup tidak dibuatkan
    record-nya — tidak fatal.
    """
    periode = models.ForeignKey(BiayaKuliahPeriode, on_delete=models.CASCADE,
                                related_name='detail')
    prodi = models.ForeignKey(Prodi, on_delete=models.CASCADE)
    jenis_kelas = models.CharField(max_length=20, choices=KELAS_CHOICES)

    # Field schema standar (Reguler / Karyawan / Internasional)
    dpp_cicilan_1         = models.DecimalField(max_digits=12, decimal_places=2, default=0,
                                                verbose_name='DPP Cicilan 1')
    dpp_cicilan_2         = models.DecimalField(max_digits=12, decimal_places=2, default=0,
                                                verbose_name='DPP Cicilan 2')
    spp_per_semester      = models.DecimalField(max_digits=12, decimal_places=2, default=0,
                                                verbose_name='SPP / Semester')
    biaya_saat_registrasi = models.DecimalField(max_digits=12, decimal_places=2, default=0,
                                                verbose_name='Biaya Saat Registrasi')

    # Field schema International Boarding Class (rincian beda)
    pengembangan          = models.DecimalField(max_digits=12, decimal_places=2, default=0,
                                                verbose_name='Pengembangan (boarding)')
    biaya_hidup           = models.DecimalField(max_digits=12, decimal_places=2, default=0,
                                                verbose_name='Biaya Hidup (boarding)')
    dpp_spp_total         = models.DecimalField(max_digits=12, decimal_places=2, default=0,
                                                verbose_name='DPP & SPP Total (boarding)')

    class Meta:
        db_table = 'biaya_kuliah_prodi'
        verbose_name = 'Rincian Biaya Prodi'
        verbose_name_plural = 'Rincian Biaya Prodi'
        unique_together = ('periode', 'prodi', 'jenis_kelas')
        ordering = ['periode', 'jenis_kelas', 'prodi__fakultas', 'prodi__nama']

    def __str__(self):
        return f'{self.prodi.nama} — {self.get_jenis_kelas_display()} ({self.periode.tahun_pmb})'

    @property
    def dpp_total(self):
        return self.dpp_cicilan_1 + self.dpp_cicilan_2

    @property
    def harga_boarding(self):
        """Total harga boarding (jumlah tiga komponen)."""
        return self.pengembangan + self.biaya_hidup + self.dpp_spp_total

    @property
    def is_boarding(self):
        return self.jenis_kelas == 'boarding'
