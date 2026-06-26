import random
import string
from django.db import models
from django.contrib.auth.models import User
from core.models import Prodi, Kota, Provinsi
from core.validators import (
    validate_document_ext, validate_document_size,
    validate_image_ext, validate_image_size,
)

_DOC_V = [validate_document_ext, validate_document_size]
_IMG_V = [validate_image_ext, validate_image_size]


JALUR = (
    ('raport', 'Jalur Raport'),
    ('beasiswa', 'Jalur Beasiswa'),
    ('umum', 'Jalur Umum'),
)

AGAMA = (
    ('Islam', 'Islam'), ('Kristen', 'Kristen'), ('Katolik', 'Katolik'),
    ('Hindu', 'Hindu'), ('Budha', 'Budha'), ('Konghucu', 'Konghucu'),
)

JENIS_KELAMIN = (('L', 'Laki-laki'), ('P', 'Perempuan'))

HUBUNGAN = (('ayah', 'Ayah'), ('ibu', 'Ibu'))

PENDIDIKAN = (
    ('SD', 'SD'), ('SMP', 'SMP'), ('SMA/SMK', 'SMA/SMK'),
    ('D3', 'D3'), ('S1', 'S1'), ('S2', 'S2'), ('S3', 'S3'),
)

AKREDITASI = (('A', 'A'), ('B', 'B'), ('C', 'C'), ('Belum', 'Belum Terakreditasi'))

SUMBER_INFO = (
    ('web', 'Web'),
    ('keluarga', 'Keluarga'),
    ('sekolah', 'Sekolah'),
    ('marketing', 'Marketing / Perekomendasi'),
    ('lainnya', 'Lainnya'),
)


def upload_registrasi(instance, filename):
    return f'berkas/registrasi/{instance.pendaftar.no_daftar}_{filename}'


def generate_no_daftar(jalur):
    from django.utils import timezone
    prefix = {'raport': 'R', 'beasiswa': 'B', 'umum': 'U'}.get(jalur, 'X')
    tahun = timezone.now().year
    while True:
        kode = ''.join(random.choices(string.digits, k=6))
        no = f'{prefix}{tahun}{kode}'
        if not Pendaftar.objects.filter(no_daftar=no).exists():
            return no


class Pendaftar(models.Model):
    user = models.OneToOneField(User, on_delete=models.CASCADE)
    no_daftar = models.CharField(max_length=20, unique=True)
    nama = models.CharField(max_length=200)
    NIK = models.CharField(max_length=16)
    no_kk = models.CharField(max_length=16, blank=True)
    jenis_kelamin = models.CharField(max_length=1, choices=JENIS_KELAMIN)
    tempat_lahir = models.CharField(max_length=100)
    tanggal_lahir = models.DateField(null=True, blank=True)
    agama = models.CharField(max_length=20, choices=AGAMA)
    no_hp = models.CharField(max_length=20)
    no_telp_rumah = models.CharField(max_length=20, blank=True)
    kode_negara = models.CharField(max_length=5, default='ID')
    jalur = models.CharField(max_length=10, choices=JALUR)
    prodi1 = models.ForeignKey(Prodi, on_delete=models.PROTECT, related_name='pendaftar_prodi1')
    prodi2 = models.ForeignKey(
        Prodi, on_delete=models.PROTECT, related_name='pendaftar_prodi2',
        null=True, blank=True
    )
    pas_foto = models.ImageField(upload_to='foto/', null=True, blank=True, validators=_IMG_V)
    scan_ktp = models.FileField(upload_to='ktp/', null=True, blank=True, validators=_DOC_V)
    sumber_info = models.CharField(max_length=20, choices=SUMBER_INFO, blank=True)
    sumber_info_nama = models.CharField(max_length=100, blank=True)
    sumber_info_hp = models.CharField(max_length=20, blank=True)
    created_at = models.DateTimeField(auto_now_add=True)

    class Meta:
        db_table = 'pendaftar'

    def __str__(self):
        return f'{self.no_daftar} - {self.nama}'


class Alamat(models.Model):
    pendaftar = models.OneToOneField(Pendaftar, on_delete=models.CASCADE)
    jalan = models.TextField()
    rt = models.CharField(max_length=5, blank=True)
    rw = models.CharField(max_length=5, blank=True)
    kelurahan = models.CharField(max_length=100)
    kota = models.ForeignKey(Kota, on_delete=models.PROTECT, null=True, blank=True)
    provinsi = models.ForeignKey(Provinsi, on_delete=models.PROTECT, null=True, blank=True)
    is_wna = models.BooleanField(default=False)

    class Meta:
        db_table = 'alamat'

    def __str__(self):
        return f'Alamat - {self.pendaftar.nama}'


class Sekolah(models.Model):
    pendaftar = models.OneToOneField(Pendaftar, on_delete=models.CASCADE)
    nama = models.CharField(max_length=200)
    jurusan = models.CharField(max_length=100)
    nisn = models.CharField(max_length=20, blank=True)
    akreditasi = models.CharField(max_length=10, choices=AKREDITASI)
    tahun_lulus = models.IntegerField()

    class Meta:
        db_table = 'sekolah'

    def __str__(self):
        return f'{self.nama} - {self.pendaftar.nama}'


class Ortu(models.Model):
    pendaftar = models.ForeignKey(Pendaftar, on_delete=models.CASCADE, related_name='ortu')
    nama = models.CharField(max_length=200)
    hubungan = models.CharField(max_length=4, choices=HUBUNGAN)
    pekerjaan = models.CharField(max_length=100, blank=True)
    pendidikan = models.CharField(max_length=10, choices=PENDIDIKAN, blank=True)
    penghasilan = models.BigIntegerField(null=True, blank=True)
    no_hp = models.CharField(max_length=20, blank=True)

    class Meta:
        db_table = 'ortu'

    def __str__(self):
        return f'{self.get_hubungan_display()} - {self.nama}'


STATUS_REGISTRASI = (
    (0, 'Belum Daftar Ulang'),
    (1, 'Sudah Daftar Ulang'),
    (2, 'Batal / Undur Diri'),
)

KELAS_REGISTRASI = (
    ('reguler', 'Reguler'),
    ('karyawan', 'Karyawan'),
    ('internasional', 'Internasional'),
    ('boarding', 'International Boarding'),
)


class Registrasi(models.Model):
    pendaftar      = models.OneToOneField(Pendaftar, on_delete=models.CASCADE, related_name='registrasi')
    status         = models.IntegerField(choices=STATUS_REGISTRASI, default=0)
    kelas          = models.CharField(max_length=20, choices=KELAS_REGISTRASI, blank=True, default='',
                                      help_text='Kelas pilihan cama. Sertifikat lulus baru bisa dicetak '
                                                'setelah kelas dipilih.')
    tgl_registrasi = models.DateTimeField(null=True, blank=True)
    nim            = models.CharField(max_length=20, blank=True, help_text='NIM yang diberikan setelah daftar ulang')
    nomor_va       = models.CharField(max_length=40, blank=True, default='',
                                      help_text='Nomor Virtual Account pembayaran (dari Sistem Keuangan)')
    catatan        = models.TextField(blank=True)
    diproses_oleh  = models.CharField(max_length=100, blank=True)

    # Berkas registrasi (diupload oleh cama setelah dinyatakan lulus)
    pas_foto                 = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_IMG_V)
    bukti_bayar_pendaftaran  = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    bukti_bayar_sks          = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    bukti_bayar_pengembangan = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    akte_lahir               = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    ijazah                   = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    skhun                    = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    kartu_keluarga           = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    hasil_tes_kesehatan      = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)
    hasil_tes_mmpi2          = models.FileField(upload_to=upload_registrasi, blank=True, null=True, validators=_DOC_V)

    class Meta:
        db_table = 'registrasi'

    def __str__(self):
        return f'Registrasi - {self.pendaftar.nama} ({self.get_status_display()})'

    @property
    def prodi_lulus(self):
        jalur = self.pendaftar.jalur
        try:
            if jalur == 'raport':
                return self.pendaftar.raport_berkas.prodi_lulus
            elif jalur == 'beasiswa':
                return self.pendaftar.beasiswa_daftar.prodi_lulus
            elif jalur == 'umum':
                return self.pendaftar.umum_daftar.prodi_lulus
        except Exception:
            pass
        return None
