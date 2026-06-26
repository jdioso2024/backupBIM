from django.urls import path
from . import views

urlpatterns = [
    path('berkas/', views.berkas, name='raport_berkas'),
    path('nilai/tambah/', views.tambah_nilai, name='raport_tambah_nilai'),
    path('nilai/hapus/<int:pk>/', views.hapus_nilai, name='raport_hapus_nilai'),
]
