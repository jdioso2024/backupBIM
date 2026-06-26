from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from .models import BeasiswaDaftar, BeasiswaPrestasi
from .forms import BeasiswaDaftarForm, BeasiswaPrestasiForm


def _get_pendaftar_beasiswa(request):
    try:
        p = request.user.pendaftar
        if p.jalur != 'beasiswa':
            return None
        return p
    except Exception:
        return None


@login_required
def berkas(request):
    pendaftar = _get_pendaftar_beasiswa(request)
    if not pendaftar:
        return redirect('dashboard')

    obj, _ = BeasiswaDaftar.objects.get_or_create(
        pendaftar=pendaftar,
        defaults={'jenis_beasiswa': 'LAIN', 'batch': 1}
    )

    if request.method == 'POST':
        form = BeasiswaDaftarForm(request.POST, request.FILES, instance=obj)
        if form.is_valid():
            form.save()
            messages.success(request, 'Berkas beasiswa berhasil disimpan.')
            return redirect('beasiswa_berkas')
    else:
        form = BeasiswaDaftarForm(instance=obj)

    prestasi_list = BeasiswaPrestasi.objects.filter(beasiswa=obj)
    return render(request, 'beasiswa/berkas.html', {
        'form': form, 'pendaftar': pendaftar,
        'beasiswa': obj, 'prestasi_list': prestasi_list,
    })


@login_required
def tambah_prestasi(request):
    pendaftar = _get_pendaftar_beasiswa(request)
    if not pendaftar:
        return redirect('dashboard')

    beasiswa = get_object_or_404(BeasiswaDaftar, pendaftar=pendaftar)

    if request.method == 'POST':
        form = BeasiswaPrestasiForm(request.POST, request.FILES)
        if form.is_valid():
            p = form.save(commit=False)
            p.beasiswa = beasiswa
            p.save()
            messages.success(request, 'Prestasi berhasil ditambahkan.')
            return redirect('beasiswa_berkas')
    else:
        form = BeasiswaPrestasiForm()

    return render(request, 'beasiswa/tambah_prestasi.html', {'form': form, 'pendaftar': pendaftar})


@login_required
def hapus_prestasi(request, pk):
    pendaftar = _get_pendaftar_beasiswa(request)
    if not pendaftar:
        return redirect('dashboard')
    beasiswa = get_object_or_404(BeasiswaDaftar, pendaftar=pendaftar)
    obj = get_object_or_404(BeasiswaPrestasi, pk=pk, beasiswa=beasiswa)
    obj.delete()
    return redirect('beasiswa_berkas')
