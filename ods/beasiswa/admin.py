from django.contrib import admin
from .models import BeasiswaDaftar, BeasiswaPrestasi, JenisBeasiswa


class BeasiswaPrestasiInline(admin.TabularInline):
    model = BeasiswaPrestasi
    extra = 0


@admin.register(BeasiswaDaftar)
class BeasiswaDaftarAdmin(admin.ModelAdmin):
    list_display = ('pendaftar', 'jenis_beasiswa', 'batch', 'status_seleksi', 'prodi_lulus')
    list_filter = ('jenis_beasiswa', 'batch', 'status_seleksi')
    search_fields = ('pendaftar__nama', 'pendaftar__no_daftar')
    inlines = [BeasiswaPrestasiInline]


@admin.register(JenisBeasiswa)
class JenisBeasiswaAdmin(admin.ModelAdmin):
    list_display = ('kode', 'nama', 'urutan', 'aktif')
    list_editable = ('urutan', 'aktif')
    search_fields = ('kode', 'nama')
    list_filter = ('aktif',)
    ordering = ('urutan', 'nama')
