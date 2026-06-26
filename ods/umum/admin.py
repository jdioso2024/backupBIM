from django.contrib import admin
from .models import UmumDaftar


@admin.register(UmumDaftar)
class UmumDaftarAdmin(admin.ModelAdmin):
    list_display = ('pendaftar', 'status', 'skor_cbt', 'prodi_lulus')
    list_filter = ('status',)
    search_fields = ('pendaftar__nama', 'pendaftar__no_daftar')
