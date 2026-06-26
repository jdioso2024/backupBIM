from django.urls import path
from . import views

urlpatterns = [
    path('daftar/', views.daftar, name='umum_daftar'),
]
