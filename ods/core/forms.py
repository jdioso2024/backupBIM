from django import forms
from .models import SiteSetting, IntegrasiConfig


class BootstrapFormMixin:
    """Terapkan class='form-control' ke semua widget input/select/textarea.
    Checkbox, radio, dan file tetap default (Bootstrap punya styling terpisah)."""

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        for name, field in self.fields.items():
            w = field.widget
            if isinstance(w, (forms.TextInput, forms.NumberInput, forms.EmailInput,
                              forms.URLInput, forms.PasswordInput, forms.DateInput,
                              forms.Textarea, forms.Select, forms.SelectMultiple)):
                w.attrs.setdefault('class', 'form-control')
            elif isinstance(w, (forms.CheckboxInput, forms.RadioSelect)):
                pass
            else:
                w.attrs.setdefault('class', 'form-control')


class SiteSettingForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = SiteSetting
        fields = [
            'nama_universitas', 'singkatan', 'alamat', 'kota', 'kode_pos',
            'telepon', 'website', 'email_pmb',
            'nama_program', 'tahun_pmb', 'tahun_ajaran', 'footer_copyright',
            'logo', 'logo_kop', 'logo_login', 'favicon',
            'nama_pimpinan', 'nip_pimpinan', 'jabatan_pimpinan', 'kota_ttd',
        ]
        widgets = {
            'alamat': forms.Textarea(attrs={'rows': 3}),
        }


class IntegrasiConfigForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = IntegrasiConfig
        fields = [
            'keuangan_aktif', 'keuangan_api_base',
            'keuangan_ods_token', 'keuangan_timeout',
        ]
        widgets = {
            'keuangan_api_base': forms.URLInput(attrs={'placeholder': 'https://keuangan.example.ac.id'}),
            'keuangan_ods_token': forms.TextInput(attrs={'autocomplete': 'off', 'style': 'font-family:monospace'}),
        }
