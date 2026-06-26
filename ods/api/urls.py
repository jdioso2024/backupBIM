from django.urls import path
from . import views

app_name = 'api'

urlpatterns = [
    path('rekap/',                views.rekap,               name='rekap'),
    path('rekap-pasca/',          views.rekap_pasca,         name='rekap_pasca'),
    path('program-studi/',        views.program_studi,       name='program_studi'),
    path('laporan-registrasi/',   views.laporan_registrasi,  name='laporan_registrasi'),
    path('data-detail/',          views.data_detail,         name='data_detail'),
    path('perbandingan-tahun/',   views.perbandingan_tahun,  name='perbandingan_tahun'),
    path('sebaran-domisili/',     views.sebaran_domisili,    name='sebaran_domisili'),
    path('sebaran-sekolah/',      views.sebaran_sekolah,     name='sebaran_sekolah'),
]
