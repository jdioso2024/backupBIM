from django.contrib import admin
from .models import Pendaftar, Alamat, Sekolah, Ortu


class AlamatInline(admin.StackedInline):
    model = Alamat
    extra = 0


class SekolahInline(admin.StackedInline):
    model = Sekolah
    extra = 0


class OrtuInline(admin.TabularInline):
    model = Ortu
    extra = 0


@admin.register(Pendaftar)
class PendaftarAdmin(admin.ModelAdmin):
    list_display = ('no_daftar', 'nama', 'jalur', 'prodi1', 'created_at')
    list_filter = ('jalur', 'jenis_kelamin', 'agama')
    search_fields = ('no_daftar', 'nama', 'NIK')
    inlines = [AlamatInline, SekolahInline, OrtuInline]
