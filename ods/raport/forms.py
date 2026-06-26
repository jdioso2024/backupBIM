from django import forms
from .models import RaportBerkas, RaportNilai
from core.forms import BootstrapFormMixin


class RaportBerkasForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = RaportBerkas
        fields = [
            'file_raport',
            'file_prestasi_1', 'tingkat_prestasi_1',
            'file_prestasi_2', 'tingkat_prestasi_2',
            'file_prestasi_3', 'tingkat_prestasi_3',
        ]


class RaportNilaiForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = RaportNilai
        fields = ['mata_pelajaran', 'semester', 'nilai']
        widgets = {
            'mata_pelajaran': forms.TextInput(attrs={'placeholder': 'Contoh: Matematika'}),
            'semester':       forms.NumberInput(attrs={'min': 1, 'max': 6}),
            'nilai':          forms.NumberInput(attrs={'min': 0, 'max': 100, 'step': '0.01'}),
        }
