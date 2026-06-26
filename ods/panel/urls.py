from django.urls import path
from . import views

app_name = 'panel'

urlpatterns = [
    path('login/',                       views.login_admin,     name='login'),
    path('logout/',                      views.logout_admin,    name='logout'),
    path('',                             views.dashboard,       name='dashboard'),
    path('validasi/',                    views.validasi_list,   name='validasi_list'),
    path('validasi/<str:jalur>/<int:pk>/', views.validasi_detail, name='validasi_detail'),
    path('kelulusan/',                   views.kelulusan_list,  name='kelulusan_list'),
    path('kelulusan/<str:jalur>/<int:pk>/', views.kelulusan_set, name='kelulusan_set'),
    path('registrasi/',                  views.registrasi_list, name='registrasi_list'),
    path('registrasi/<int:pk>/',         views.registrasi_set,  name='registrasi_set'),
    path('cari-cama/',                   views.cari_cama,       name='cari_cama'),
    path('cari-cama/<int:pk>/',          views.cari_cama_detail, name='cari_cama_detail'),
    path('cari-cama/<int:pk>/login-as/', views.login_as,         name='login_as'),
    path('cari-cama/<int:pk>/reset-password/', views.cari_cama_reset_password, name='cari_cama_reset_password'),
    path('exit-login-as/',               views.exit_login_as,    name='exit_login_as'),
    path('kelola-prodi/',                views.kelola_prodi_list,    name='kelola_prodi_list'),
    path('jenis-beasiswa/',              views.jenis_beasiswa_list,  name='jenis_beasiswa_list'),
    path('jenis-beasiswa/baru/',         views.jenis_beasiswa_form,  name='jenis_beasiswa_new'),
    path('jenis-beasiswa/<int:pk>/ubah/', views.jenis_beasiswa_form, name='jenis_beasiswa_edit'),
    path('jenis-beasiswa/<int:pk>/hapus/', views.jenis_beasiswa_delete, name='jenis_beasiswa_delete'),
    path('pengaturan/',                  views.pengaturan,      name='pengaturan'),
    path('integrasi-api/',               views.integrasi_config, name='integrasi_config'),
    path('import-pendaftar/',            views.import_pendaftar,          name='import_pendaftar'),
    path('import-pendaftar/template/',   views.import_pendaftar_template, name='import_pendaftar_template'),
    path('cleanup-duplikat/',            views.cleanup_duplikat, name='cleanup_duplikat'),
    path('kelola-user/',                 views.kelola_user_list,   name='kelola_user_list'),
    path('kelola-user/baru/',            views.kelola_user_form,   name='kelola_user_new'),
    path('kelola-user/<int:pk>/ubah/',   views.kelola_user_form,   name='kelola_user_edit'),
    path('kelola-user/<int:pk>/hapus/',  views.kelola_user_delete, name='kelola_user_delete'),
    path('kelola-user/<int:pk>/reset-password/', views.kelola_user_reset_password, name='kelola_user_reset_password'),
    path('registrasi/<int:pk>/sertifikat/', views.pdf_sertifikat_lulus, name='pdf_sertifikat_lulus'),
    path('registrasi/<int:pk>/bukti-reg/',  views.pdf_bukti_reg,        name='pdf_bukti_reg'),
    path('registrasi/<int:pk>/ktm/',        views.pdf_ktm,              name='pdf_ktm'),
]
