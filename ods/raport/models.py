from django.db import models
from pendaftaran.models import Pendaftar
from core.models import Prodi
from core.validators import validate_document_ext, validate_document_size

_DOC_V = [validate_document_ext, validate_document_size]


STATUS = (
    (0, 'Belum Lengkap'),
    (1, 'Menunggu Validasi'),
    (2, 'Valid'),
    (3, 'Lulus'),
    (4, 'Tidak Lulus'),
    (5, 'Perbaikan'),
    (6, 'Undur Diri'),
)

TINGKAT_PRESTASI = (
    ('sekolah', 'Tingkat Sekolah'),
    ('kabkota', 'Tingkat Kab/Kota'),
    ('provinsi', 'Tingkat Provinsi'),
    ('nasional', 'Tingkat Nasional'),
    ('internasional', 'Tingkat Internasional'),
)


class RaportBerkas(models.Model):
    pendaftar = models.OneToOneField(Pendaftar, on_delete=models.CASCADE, related_name='raport_berkas')
    file_raport = models.FileField(upload_to='raport/berkas/', null=True, blank=True, validators=_DOC_V)
    file_prestasi_1 = models.FileField(upload_to='raport/prestasi/', null=True, blank=True, validators=_DOC_V)
    file_prestasi_2 = models.FileField(upload_to='raport/prestasi/', null=True, blank=True, validators=_DOC_V)
    file_prestasi_3 = models.FileField(upload_to='raport/prestasi/', null=True, blank=True, validators=_DOC_V)
    tingkat_prestasi_1 = models.CharField(max_length=15, choices=TINGKAT_PRESTASI, blank=True)
    tingkat_prestasi_2 = models.CharField(max_length=15, choices=TINGKAT_PRESTASI, blank=True)
    tingkat_prestasi_3 = models.CharField(max_length=15, choices=TINGKAT_PRESTASI, blank=True)
    nilai_raport = models.DecimalField(max_digits=5, decimal_places=2, null=True, blank=True)
    nilai_prestasi = models.DecimalField(max_digits=5, decimal_places=2, null=True, blank=True)
    status = models.IntegerField(choices=STATUS, default=0)
    keterangan = models.TextField(blank=True)
    prodi_lulus = models.ForeignKey(Prodi, on_delete=models.SET_NULL, null=True, blank=True)
    diproses_oleh = models.CharField(max_length=100, blank=True)
    tgl_diproses = models.DateTimeField(null=True, blank=True)

    class Meta:
        db_table = 'raport_berkas'

    def __str__(self):
        return f'Raport - {self.pendaftar.nama}'

    @property
    def nilai_total(self):
        if self.nilai_raport is None:
            return None
        akreditasi_bobot = {'A': 1.0, 'B': 0.9, 'C': 0.8, 'Belum': 0.7}
        bobot = akreditasi_bobot.get(self.pendaftar.sekolah.akreditasi, 1.0)
        prestasi = self.nilai_prestasi or 0
        return float(self.nilai_raport) * bobot + 0.1 * float(prestasi)


class RaportNilai(models.Model):
    pendaftar = models.ForeignKey(Pendaftar, on_delete=models.CASCADE, related_name='nilai_raport')
    mata_pelajaran = models.CharField(max_length=100)
    semester = models.IntegerField()
    nilai = models.DecimalField(max_digits=5, decimal_places=2)

    class Meta:
        db_table = 'raport_nilai'
        unique_together = ('pendaftar', 'mata_pelajaran', 'semester')

    def __str__(self):
        return f'{self.mata_pelajaran} Sem-{self.semester}: {self.nilai}'
