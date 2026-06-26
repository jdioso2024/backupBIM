from django.shortcuts import render, redirect
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from .models import UmumDaftar


@login_required
def daftar(request):
    try:
        pendaftar = request.user.pendaftar
        if pendaftar.jalur != 'umum':
            return redirect('dashboard')
    except Exception:
        return redirect('dashboard')

    obj, _ = UmumDaftar.objects.get_or_create(pendaftar=pendaftar)
    return render(request, 'umum/daftar.html', {'pendaftar': pendaftar, 'umum': obj})
