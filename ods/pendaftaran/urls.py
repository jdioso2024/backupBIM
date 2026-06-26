from django.urls import path, reverse_lazy
from django.contrib.auth import views as auth_views
from . import views

urlpatterns = [
    path('', views.home, name='home'),
    path('daftar/', views.register, name='register'),
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),
    path('dashboard/', views.dashboard, name='dashboard'),
    path('data-diri/', views.data_diri, name='data_diri'),
    path('upload-ktp/', views.upload_ktp, name='upload_ktp'),
    path('data-sekolah/', views.data_sekolah, name='data_sekolah'),
    path('data-ortu/', views.data_ortu, name='data_ortu'),
    path('pantau-status/', views.pantau_status, name='pantau_status'),
    path('pilih-kelas/', views.pilih_kelas, name='pilih_kelas'),
    path('registrasi/', views.registrasi, name='registrasi'),
    path('sertifikat-lulus/pdf/', views.sertifikat_lulus_pdf, name='sertifikat_lulus_pdf'),
    path('registrasi/bukti-reg/', views.cetak_bukti_reg,     name='cetak_bukti_reg'),
    path('registrasi/ktm/',       views.cetak_ktm,           name='cetak_ktm'),
    path('ganti-password/', views.ganti_password, name='ganti_password'),

    # Forgot password flow (Django built-in views, custom templates)
    path('lupa-password/',
         auth_views.PasswordResetView.as_view(
             template_name='pendaftaran/password_reset_form.html',
             email_template_name='pendaftaran/password_reset_email.html',
             subject_template_name='pendaftaran/password_reset_subject.txt',
             success_url=reverse_lazy('password_reset_done'),
         ),
         name='password_reset'),
    path('lupa-password/terkirim/',
         auth_views.PasswordResetDoneView.as_view(
             template_name='pendaftaran/password_reset_done.html',
         ),
         name='password_reset_done'),
    path('reset/<uidb64>/<token>/',
         auth_views.PasswordResetConfirmView.as_view(
             template_name='pendaftaran/password_reset_confirm.html',
             success_url=reverse_lazy('password_reset_complete'),
         ),
         name='password_reset_confirm'),
    path('reset/selesai/',
         auth_views.PasswordResetCompleteView.as_view(
             template_name='pendaftaran/password_reset_complete.html',
         ),
         name='password_reset_complete'),
]
