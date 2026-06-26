from django.db import models
from pendaftaran.models import Pendaftar
from core.models import Prodi
from core.validators import validate_document_ext, validate_document_size

_DOC_V = [validate_document_ext, validate_document_size]


class JenisBeasiswa(models.Model):
    """Master jenis beasiswa yang ditawarkan PMB. Dikelola via panel admin."""
    kode = models.CharField(max_length=20, unique=True,
                            help_text='Kode unik (mis: KIP, BTUMD). Akan disimpan pada record pendaftaran.')
    nama = models.CharField(max_length=150)
    deskripsi = models.TextField(blank=True)
    aktif = models.BooleanField(default=True,
                                help_text='Jika dimatikan, jenis ini tidak muncul pada form pendaftaran cama.')
    urutan = models.IntegerField(default=0,
                                 help_text='Semakin kecil semakin atas.')

    class Meta:
        db_table = 'beasiswa_jenis'
        ordering = ['urutan', 'nama']
        verbose_name = 'Jenis Beasiswa'
        verbose_name_plural = 'Jenis Beasiswa'

    def __str__(self):
        return f'{self.kode} — {self.nama}'

    @classmethod
    def choices_aktif(cls):
        """Choices (kode, nama) untuk form/cama — hanya yang aktif."""
        return [(j.kode, j.nama) for j in cls.objects.filter(aktif=True)]

    @classmethod
    def label_for(cls, kode):
        """Resolve label dari kode (untuk get_jenis_beasiswa_display()).
        Fallback ke kode itu sendiri kalau record sudah dihapus."""
        if not kode:
            return ''
        obj = cls.objects.filter(kode=kode).first()
        return obj.nama if obj else kode


STATUS_SELEKSI = (
    (0, 'Melengkapi Data'),
    (1, 'Proses Seleksi'),
    (2, 'Terverifikasi'),
    (3, 'Lolos'),
    (4, 'Tidak Lolos'),
)

TINGKAT_PRESTASI = (
    ('sekolah', 'Tingkat Sekolah'),
    ('kabkota', 'Tingkat Kab/Kota'),
    ('provinsi', 'Tingkat Provinsi'),
    ('nasional', 'Tingkat Nasional'),
    ('internasional', 'Tingkat Internasional'),
)


class BeasiswaDaftar(models.Model):
    pendaftar = models.OneToOneField(Pendaftar, on_delete=models.CASCADE, related_name='beasiswa_daftar')
    jenis_beasiswa = models.CharField(max_length=20,
                                      help_text='Kode JenisBeasiswa yang dipilih cama.')
    batch = models.IntegerField(default=1)
    status_seleksi = models.IntegerField(choices=STATUS_SELEKSI, default=0)
    file_formulir = models.FileField(upload_to='beasiswa/berkas/', null=True, blank=True, validators=_DOC_V)
    file_penghasilan = models.FileField(upload_to='beasiswa/berkas/', null=True, blank=True, validators=_DOC_V)
    file_rekomendasi = models.FileField(upload_to='beasiswa/berkas/', null=True, blank=True, validators=_DOC_V)
    file_raport = models.FileField(upload_to='beasiswa/berkas/', null=True, blank=True, validators=_DOC_V)
    file_prestasi = models.FileField(upload_to='beasiswa/berkas/', null=True, blank=True, validators=_DOC_V)
    file_toefl = models.FileField(upload_to='beasiswa/berkas/', null=True, blank=True, validators=_DOC_V)
    keterangan = models.TextField(blank=True)
    catatan_panitia = models.TextField(blank=True)
    prodi_lulus = models.ForeignKey(Prodi, on_delete=models.SET_NULL, null=True, blank=True)
    diproses_oleh = models.CharField(max_length=100, blank=True)
    tgl_diproses = models.DateTimeField(null=True, blank=True)

    class Meta:
        db_table = 'beasiswa_daftar'

    def get_jenis_beasiswa_display(self):
        return JenisBeasiswa.label_for(self.jenis_beasiswa)

    def __str__(self):
        return f'{self.get_jenis_beasiswa_display()} - {self.pendaftar.nama}'


class BeasiswaPrestasi(models.Model):
    beasiswa = models.ForeignKey(BeasiswaDaftar, on_delete=models.CASCADE, related_name='prestasi')
    nama = models.CharField(max_length=200)
    tingkat = models.CharField(max_length=15, choices=TINGKAT_PRESTASI)
    tahun = models.IntegerField()
    file = models.FileField(upload_to='beasiswa/prestasi/', validators=_DOC_V)

    class Meta:
        db_table = 'beasiswa_prestasi'

    def __str__(self):
        return f'{self.nama} ({self.get_tingkat_display()}, {self.tahun})'
