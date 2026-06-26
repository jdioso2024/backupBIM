from django.contrib import admin
from .models import RaportBerkas, RaportNilai


@admin.register(RaportBerkas)
class RaportBerkasAdmin(admin.ModelAdmin):
    list_display = ('pendaftar', 'nilai_raport', 'nilai_prestasi', 'status', 'prodi_lulus')
    list_filter = ('status',)
    search_fields = ('pendaftar__nama', 'pendaftar__no_daftar')

    def get_queryset(self, request):
        return super().get_queryset(request).select_related('pendaftar', 'prodi_lulus')


@admin.register(RaportNilai)
class RaportNilaiAdmin(admin.ModelAdmin):
    list_display = ('pendaftar', 'mata_pelajaran', 'semester', 'nilai')
    search_fields = ('pendaftar__nama',)
