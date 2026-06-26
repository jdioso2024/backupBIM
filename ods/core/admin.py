from django.contrib import admin
from django.urls import path
from django.shortcuts import redirect
from .models import (
    Provinsi, Kota, Prodi, Setting, SiteSetting,
    BiayaKuliahPeriode, BiayaKuliahProdi, IntegrasiConfig,
)


@admin.register(IntegrasiConfig)
class IntegrasiConfigAdmin(admin.ModelAdmin):
    fieldsets = (
        ('Koneksi Keluar — Sistem Keuangan', {
            'fields': ('keuangan_aktif', 'keuangan_api_base',
                       'keuangan_ods_token', 'keuangan_timeout'),
            'description': 'PMB Terpadu mengirim tagihan CAMA ke Sistem Keuangan '
                           '(POST /api/integrations/ods/tagihan/, header X-API-Key).',
        }),
    )
    readonly_fields = ('diperbarui',)

    def has_add_permission(self, request):
        return not IntegrasiConfig.objects.exists()

    def has_delete_permission(self, request, obj=None):
        return False


@admin.register(SiteSetting)
class SiteSettingAdmin(admin.ModelAdmin):
    fieldsets = (
        ('Identitas Universitas', {
            'fields': ('nama_universitas', 'singkatan', 'alamat', 'kota', 'kode_pos',
                       'telepon', 'website', 'email_pmb'),
        }),
        ('Program PMB', {
            'fields': ('nama_program', 'tahun_pmb', 'tahun_ajaran', 'footer_copyright'),
        }),
        ('Logo & Favicon', {
            'fields': ('logo', 'logo_kop', 'favicon'),
            'description': 'Logo digunakan pada KTM, sertifikat, dan kop surat.',
        }),
        ('Pejabat Penandatangan Dokumen', {
            'fields': ('nama_pimpinan', 'nip_pimpinan', 'jabatan_pimpinan', 'kota_ttd'),
            'description': 'Data ini muncul pada sertifikat lulus, sertifikat registrasi, dan KTM.',
        }),
    )

    def has_add_permission(self, request):
        return not SiteSetting.objects.exists()

    def has_delete_permission(self, request, obj=None):
        return False

    def get_urls(self):
        urls = super().get_urls()
        custom = [
            path('', self.admin_site.admin_view(self.redirect_to_change), name='core_sitesetting_changelist'),
        ]
        return custom + urls

    def redirect_to_change(self, request):
        obj = SiteSetting.get_instance()
        return redirect(f'/admin/core/sitesetting/{obj.pk}/change/')


@admin.register(Setting)
class SettingAdmin(admin.ModelAdmin):
    list_display = ('key', 'value', 'keterangan')
    search_fields = ('key',)


@admin.register(Prodi)
class ProdiAdmin(admin.ModelAdmin):
    list_display = ('kode', 'nama', 'jenjang', 'fakultas', 'kuota', 'aktif')
    list_filter = ('jenjang', 'fakultas', 'aktif')
    search_fields = ('nama', 'kode', 'FID')
    list_editable = ('kuota', 'aktif')


@admin.register(Kota)
class KotaAdmin(admin.ModelAdmin):
    list_display = ('nama', 'provinsi')
    list_filter = ('provinsi',)
    search_fields = ('nama',)


admin.site.register(Provinsi)


class BiayaKuliahProdiInline(admin.TabularInline):
    model = BiayaKuliahProdi
    extra = 0
    autocomplete_fields = ('prodi',)
    fields = ('prodi', 'jenis_kelas',
              'dpp_cicilan_1', 'dpp_cicilan_2', 'spp_per_semester', 'biaya_saat_registrasi',
              'pengembangan', 'biaya_hidup', 'dpp_spp_total')


@admin.register(BiayaKuliahPeriode)
class BiayaKuliahPeriodeAdmin(admin.ModelAdmin):
    list_display = ('tahun_pmb', 'biaya_pendaftaran', 'biaya_pkkmb', 'aktif', 'updated_at')
    list_filter = ('aktif',)
    list_editable = ('aktif',)
    search_fields = ('tahun_pmb',)
    inlines = [BiayaKuliahProdiInline]
    fieldsets = (
        (None, {
            'fields': ('tahun_pmb', 'aktif'),
        }),
        ('Biaya Umum', {
            'fields': ('biaya_pendaftaran', 'biaya_pkkmb'),
            'description': 'Biaya yang berlaku untuk semua prodi & kelas.',
        }),
        ('Keterangan di Sertifikat', {
            'fields': ('keterangan',),
            'description': 'Teks keterangan pembayaran yang ikut tercetak di sertifikat lulus.',
        }),
    )


@admin.register(BiayaKuliahProdi)
class BiayaKuliahProdiAdmin(admin.ModelAdmin):
    list_display = ('periode', 'prodi', 'jenis_kelas', 'dpp_cicilan_1',
                    'dpp_cicilan_2', 'spp_per_semester', 'biaya_saat_registrasi',
                    'harga_boarding')
    list_filter = ('periode__tahun_pmb', 'jenis_kelas', 'prodi__fakultas')
    search_fields = ('prodi__nama', 'prodi__kode')
    autocomplete_fields = ('prodi',)
    fieldsets = (
        (None, {'fields': ('periode', 'prodi', 'jenis_kelas')}),
        ('Reguler / Karyawan / Internasional', {
            'fields': ('dpp_cicilan_1', 'dpp_cicilan_2', 'spp_per_semester', 'biaya_saat_registrasi'),
            'description': 'Isi field di bawah ini untuk kelas Reguler, Karyawan, atau Internasional.',
        }),
        ('International Boarding Class', {
            'fields': ('pengembangan', 'biaya_hidup', 'dpp_spp_total'),
            'description': 'Khusus kelas International Boarding. Harga total = jumlah ketiganya.',
            'classes': ('collapse',),
        }),
    )
