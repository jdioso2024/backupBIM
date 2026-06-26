from django.shortcuts import render, redirect, get_object_or_404
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from .models import RaportBerkas, RaportNilai
from .forms import RaportBerkasForm

MAPEL_LIST = [
    'Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'Fisika', 'Kimia', 'Biologi',
    'Sejarah', 'Geografi', 'Ekonomi', 'Sosiologi', 'PKn', 'Agama', 'Seni Budaya',
    'Penjaskes', 'TIK / Informatika', 'Bahasa Asing Lain',
]


def _pendaftar_raport(request):
    try:
        p = request.user.pendaftar
        return p if p.jalur == 'raport' else None
    except Exception:
        return None


@login_required
def berkas(request):
    pendaftar = _pendaftar_raport(request)
    if not pendaftar:
        return redirect('dashboard')

    obj, _ = RaportBerkas.objects.get_or_create(pendaftar=pendaftar)

    if request.method == 'POST':
        form = RaportBerkasForm(request.POST, request.FILES, instance=obj)
        if form.is_valid():
            form.save()
            messages.success(request, 'Berkas berhasil disimpan.')
            return redirect('raport_berkas')
    else:
        form = RaportBerkasForm(instance=obj)

    return render(request, 'raport/berkas.html', {
        'form': form, 'pendaftar': pendaftar, 'berkas': obj,
    })


@login_required
def tambah_nilai(request):
    pendaftar = _pendaftar_raport(request)
    if not pendaftar:
        return redirect('dashboard')

    if request.method == 'POST':
        semester = request.POST.get('semester')
        mapel = request.POST.get('mata_pelajaran', '')
        if mapel == '__custom__':
            mapel = request.POST.get('mapel_custom', '').strip()
        nilai = request.POST.get('nilai')

        if not semester or not mapel or not nilai:
            messages.error(request, 'Semua field wajib diisi.')
        else:
            RaportNilai.objects.update_or_create(
                pendaftar=pendaftar, mata_pelajaran=mapel, semester=int(semester),
                defaults={'nilai': nilai}
            )
            messages.success(request, f'Nilai {mapel} Sem-{semester} berhasil disimpan.')
            return redirect('raport_tambah_nilai')

    nilai_qs = RaportNilai.objects.filter(pendaftar=pendaftar).order_by('semester', 'mata_pelajaran')
    semesters = sorted(set(nilai_qs.values_list('semester', flat=True)))
    nilai_semesters = [
        {'sem': s, 'items': nilai_qs.filter(semester=s)}
        for s in semesters
    ]

    return render(request, 'raport/tambah_nilai.html', {
        'pendaftar': pendaftar,
        'mapel_list': MAPEL_LIST,
        'nilai_semesters': nilai_semesters,
    })


@login_required
def hapus_nilai(request, pk):
    pendaftar = _pendaftar_raport(request)
    if not pendaftar:
        return redirect('dashboard')
    obj = get_object_or_404(RaportNilai, pk=pk, pendaftar=pendaftar)
    obj.delete()
    messages.success(request, 'Nilai berhasil dihapus.')
    return redirect('raport_tambah_nilai')
