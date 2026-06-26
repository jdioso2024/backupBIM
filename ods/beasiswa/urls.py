from django.urls import path
from . import views

urlpatterns = [
    path('berkas/', views.berkas, name='beasiswa_berkas'),
    path('prestasi/tambah/', views.tambah_prestasi, name='beasiswa_tambah_prestasi'),
    path('prestasi/hapus/<int:pk>/', views.hapus_prestasi, name='beasiswa_hapus_prestasi'),
]
