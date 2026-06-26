from django import forms
from django.contrib.auth.models import User
from django.contrib.auth.forms import PasswordChangeForm
from .models import Pendaftar, Alamat, Sekolah, Ortu, Registrasi, SUMBER_INFO
from core.models import Prodi
from core.forms import BootstrapFormMixin


class BootstrapPasswordChangeForm(BootstrapFormMixin, PasswordChangeForm):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['old_password'].widget.attrs['placeholder'] = 'Password lama'
        self.fields['new_password1'].widget.attrs['placeholder'] = 'Password baru (min 8 karakter)'
        self.fields['new_password2'].widget.attrs['placeholder'] = 'Ulangi password baru'


class RegisterForm(BootstrapFormMixin, forms.Form):
    nama = forms.CharField(max_length=200, widget=forms.TextInput(attrs={'placeholder': 'Nama lengkap sesuai KTP'}))
    email = forms.EmailField(widget=forms.EmailInput(attrs={'placeholder': 'email@contoh.com'}))
    password = forms.CharField(widget=forms.PasswordInput(attrs={'placeholder': 'Minimal 6 karakter'}))
    password2 = forms.CharField(widget=forms.PasswordInput(attrs={'placeholder': 'Ketik ulang password'}),
                                label='Konfirmasi Password')
    jalur = forms.ChoiceField(choices=Pendaftar.jalur.field.choices if hasattr(Pendaftar, 'jalur') else [])

    def clean(self):
        cleaned = super().clean()
        if cleaned.get('password') != cleaned.get('password2'):
            raise forms.ValidationError('Password tidak cocok.')
        if User.objects.filter(email=cleaned.get('email')).exists():
            raise forms.ValidationError('Email sudah terdaftar.')
        return cleaned


class DataDiriForm(BootstrapFormMixin, forms.ModelForm):
    tanggal_lahir = forms.DateField(
        required=False,
        input_formats=['%Y-%m-%d', '%d-%m-%Y', '%d/%m/%Y'],
        widget=forms.DateInput(attrs={'type': 'date'}, format='%Y-%m-%d'),
    )

    class Meta:
        model = Pendaftar
        fields = [
            'nama', 'NIK', 'no_kk', 'jenis_kelamin',
            'tempat_lahir', 'tanggal_lahir', 'agama',
            'no_hp', 'prodi1', 'prodi2',
            'sumber_info', 'sumber_info_nama', 'sumber_info_hp',
        ]
        widgets = {
            'nama':             forms.TextInput(attrs={'placeholder': 'Nama lengkap sesuai KTP', 'maxlength': 200}),
            'NIK':              forms.TextInput(attrs={'placeholder': '16 digit NIK', 'maxlength': 16, 'pattern': r'\d{16}'}),
            'no_kk':            forms.TextInput(attrs={'placeholder': '16 digit No. Kartu Keluarga', 'maxlength': 16, 'pattern': r'\d{16}'}),
            'tempat_lahir':     forms.TextInput(attrs={'placeholder': 'Kota kelahiran'}),
            'no_hp':            forms.TextInput(attrs={'placeholder': '081234567890', 'pattern': r'0\d{9,14}'}),
            'sumber_info':      forms.RadioSelect,
            'sumber_info_nama': forms.TextInput(attrs={'placeholder': 'Nama Marketing / Perekomendasi', 'maxlength': 100}),
            'sumber_info_hp':   forms.TextInput(attrs={'placeholder': '081234567890', 'maxlength': 20}),
        }
        labels = {
            'sumber_info':      'Dapat informasi PMB dari',
            'sumber_info_nama': 'Nama Marketing/Perekom',
            'sumber_info_hp':   'No. HP',
        }

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        aktif = Prodi.objects.filter(aktif=True).order_by('fakultas', 'nama')
        self.fields['prodi1'].queryset = aktif
        self.fields['prodi2'].queryset = aktif
        self.fields['prodi2'].required = False
        self.fields['sumber_info'].required = True
        # Tanpa opsi kosong "---------"; "Lainnya" jadi pilihan eksplisit.
        self.fields['sumber_info'].choices = SUMBER_INFO
        self.fields['sumber_info_nama'].required = False
        self.fields['sumber_info_hp'].required = False

    def clean(self):
        cleaned = super().clean()
        sumber = cleaned.get('sumber_info')
        if sumber == 'marketing':
            if not (cleaned.get('sumber_info_nama') or '').strip():
                self.add_error('sumber_info_nama',
                               'Nama Marketing/Perekom wajib diisi.')
            if not (cleaned.get('sumber_info_hp') or '').strip():
                self.add_error('sumber_info_hp',
                               'No. HP Marketing/Perekom wajib diisi.')
        elif sumber == 'lainnya':
            if not (cleaned.get('sumber_info_nama') or '').strip():
                self.add_error('sumber_info_nama',
                               'Mohon isi sumber informasi pada kolom "Dari".')
            cleaned['sumber_info_hp'] = ''
        else:
            cleaned['sumber_info_nama'] = ''
            cleaned['sumber_info_hp'] = ''
        return cleaned


class AlamatForm(BootstrapFormMixin, forms.ModelForm):
    is_wna = forms.TypedChoiceField(
        label='Kewarganegaraan',
        choices=[('0', 'WNI'), ('1', 'WNA')],
        coerce=lambda x: x == '1',
        widget=forms.RadioSelect,
        initial='0',
    )

    class Meta:
        model = Alamat
        fields = ['is_wna', 'jalan', 'rt', 'rw', 'kelurahan', 'kota', 'provinsi']
        widgets = {
            'jalan':     forms.Textarea(attrs={'rows': 2, 'placeholder': 'Nama jalan, nomor rumah'}),
            'rt':        forms.TextInput(attrs={'placeholder': 'RT', 'maxlength': 5}),
            'rw':        forms.TextInput(attrs={'placeholder': 'RW', 'maxlength': 5}),
            'kelurahan': forms.TextInput(attrs={'placeholder': 'Desa / Kelurahan'}),
        }

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields['kota'].required = False
        self.fields['provinsi'].required = False
        if self.instance and self.instance.pk:
            self.fields['is_wna'].initial = '1' if self.instance.is_wna else '0'

    def clean(self):
        cleaned = super().clean()
        is_wna = cleaned.get('is_wna')
        if is_wna:
            cleaned['kota'] = None
            cleaned['provinsi'] = None
        else:
            if not cleaned.get('kota'):
                self.add_error('kota', 'Kota / Kabupaten wajib dipilih untuk WNI.')
            if not cleaned.get('provinsi'):
                self.add_error('provinsi', 'Provinsi wajib dipilih untuk WNI.')
        return cleaned


class SekolahForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = Sekolah
        fields = ['nama', 'jurusan', 'nisn', 'akreditasi', 'tahun_lulus']
        widgets = {
            'nama':        forms.TextInput(attrs={'placeholder': 'Nama sekolah / madrasah'}),
            'jurusan':     forms.TextInput(attrs={'placeholder': 'IPA / IPS / Bahasa / SMK'}),
            'nisn':        forms.TextInput(attrs={'placeholder': '10 digit NISN', 'maxlength': 20}),
            'tahun_lulus': forms.NumberInput(attrs={'placeholder': 'Contoh: 2024', 'min': 1990, 'max': 2100}),
        }


class OrtuForm(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = Ortu
        fields = ['nama', 'pekerjaan', 'pendidikan', 'penghasilan', 'no_hp']
        widgets = {
            'nama':      forms.TextInput(attrs={'placeholder': 'Nama lengkap'}),
            'pekerjaan': forms.TextInput(attrs={'placeholder': 'Contoh: Petani, PNS, Wiraswasta'}),
            'no_hp':     forms.TextInput(attrs={'placeholder': '081234567890'}),
        }


class FormBerkasRegistrasi(BootstrapFormMixin, forms.ModelForm):
    class Meta:
        model = Registrasi
        fields = [
            'pas_foto',
            'bukti_bayar_pendaftaran', 'bukti_bayar_sks', 'bukti_bayar_pengembangan',
            'akte_lahir', 'ijazah', 'skhun', 'kartu_keluarga',
            'hasil_tes_kesehatan', 'hasil_tes_mmpi2',
        ]
