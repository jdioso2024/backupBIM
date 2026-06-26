from django.db import models
from pendaftaran.models import Pendaftar
from core.models import Prodi


STATUS = (
    (0, 'Belum Lengkap'),
    (1, 'Menunggu Verifikasi'),
    (2, 'Lulus'),
    (3, 'Tidak Lulus'),
)


class UmumDaftar(models.Model):
    pendaftar = models.OneToOneField(Pendaftar, on_delete=models.CASCADE, related_name='umum_daftar')
    status = models.IntegerField(choices=STATUS, default=0)
    # nullable — diisi setelah integrasi CBT
    skor_cbt = models.DecimalField(max_digits=6, decimal_places=2, null=True, blank=True)
    keterangan = models.TextField(blank=True)
    prodi_lulus = models.ForeignKey(Prodi, on_delete=models.SET_NULL, null=True, blank=True)
    diproses_oleh = models.CharField(max_length=100, blank=True)
    tgl_diproses = models.DateTimeField(null=True, blank=True)

    class Meta:
        db_table = 'umum_daftar'

    def __str__(self):
        return f'Umum - {self.pendaftar.nama}'
