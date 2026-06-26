from django import forms
from .models import BeasiswaDaftar, BeasiswaPrestasi, JenisBeasiswa
from core.forms import BootstrapFormMixin


class BeasiswaDaftarForm(BootstrapFormMixin, forms.ModelForm):
    jenis_beasiswa = forms.ChoiceField(choices=[])

    class Meta:
        model = BeasiswaDaftar
        fields = [
            'jenis_beasiswa', 'batch',
            'file_formulir', 'file_penghasilan', 'file_rekomendasi',
            'file_raport', 'file_prestasi', 'file_toefl',
        ]
        widgets = {
            'batch': forms.NumberInput(attrs={'min': 1, 'max': 10}),
        }

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        choices = JenisBeasiswa.choices_aktif()
        # Pastikan nilai existing tetap valid walau jenisnya dinonaktifkan
        if self.instance and self.instance.pk and self.instance.jenis_beasiswa:
            keys = {k for k, _ in choices}
            if self.instance.jenis_beasiswa not in keys:
                choices.append((self.instance.jenis_beasiswa,
                                JenisBeasiswa.label_for(self.instance.jenis_beasiswa)))
        self.fields['jenis_beasiswa'].choices = [('', '— pilih jenis beasiswa —')] + choices


class JenisBeasiswaForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = JenisBeasiswa
        fields = ['kode', 'nama', 'deskripsi', 'urutan', 'aktif']
        widgets = {
            'kode': forms.TextInput(attrs={'placeholder': 'Contoh: KIP, BTUMD, BU-UMS', 'style': 'text-transform:uppercase;'}),
            'nama': forms.TextInput(attrs={'placeholder': 'Contoh: KIP Kuliah'}),
            'deskripsi': forms.Textarea(attrs={'rows': 3, 'placeholder': 'Penjelasan singkat (opsional)'}),
            'urutan': forms.NumberInput(attrs={'min': 0, 'max': 999}),
        }

    def clean_kode(self):
        kode = (self.cleaned_data.get('kode') or '').strip().upper()
        if not kode:
            raise forms.ValidationError('Kode wajib diisi.')
        qs = JenisBeasiswa.objects.filter(kode=kode)
        if self.instance.pk:
            qs = qs.exclude(pk=self.instance.pk)
        if qs.exists():
            raise forms.ValidationError(f'Kode "{kode}" sudah dipakai jenis beasiswa lain.')
        return kode


class BeasiswaPrestasiForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = BeasiswaPrestasi
        fields = ['nama', 'tingkat', 'tahun', 'file']
        widgets = {
            'nama':  forms.TextInput(attrs={'placeholder': 'Contoh: Juara 1 OSN Matematika'}),
            'tahun': forms.NumberInput(attrs={'placeholder': 'Contoh: 2024', 'min': 1990, 'max': 2100}),
        }
