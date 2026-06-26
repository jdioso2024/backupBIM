<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    @include('layouts.navbar')
    @include('layouts.header')

    <div class="bg-slate-100 px-4 py-6 sm:px-32 grid sm:grid-cols-8 gap-8 text-slate-700 items-start">

        <div class="bg-blue-600 sm:col-span-8 text-xl p-4 rounded-lg">
            <div class="flex gap-4 items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6 text-amber-400">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                <p class="text-white">Sudah punya akun?</p>
                <a href="{{ route('login') }}" class="text-amber-400 font-bold underline">Masuk</a>
            </div>
        </div>

        {{-- Left Column --}}
        <div class="p-8 bg-white rounded-lg sm:col-span-5 grid gap-8">
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <strong class="font-bold">Terjadi Kesalahan!</strong>
                    <ul class="list-disc pl-5 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <strong class="font-bold">Berhasil!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"
                    role="alert">
                    <strong class="font-bold">Gagal!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif
            <h2 class="font-bold text-xl">Formulir Pendaftaran Calon Mahasiswa KIP Kuliah</h2>
            <hr class="border-slate-300">

            <form action="{{ route('student.store.kip') }}" method="POST" x-data="{ pip: false }">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select name="gender"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                    </div>
                </div>

                <div class="mt-4" x-data="emailPhoneValidation()">
                    <label class="block text-sm font-medium text-gray-700">E-Mail</label>
                    {{-- <input type="email" name="email"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required> --}}
                    <input type="email" id="email" name="email" x-model="email" @input="validateEmail()"
                        :class="{ 'border-red-500': emailError, 'border-gray-300': !emailError }"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required />
                    <p x-show="emailError" class="text-red-500 text-xs mt-1" x-text="emailError"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Asal Sekolah</label>
                        <input type="text" name="asal_sekolah"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">NISN</label>
                        <input type="number" name="nisn"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                    </div>
                </div>

                <div class="mt-4" x-data="emailPhoneValidation()">
                    <label class="block text-sm font-medium text-gray-700">No. WA</label>
                    {{-- <input type="number" name="nomor_hp"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required> --}}
                    <input type="number" id="phone" name="nomor_hp" x-model="phone" @input="validatePhone()"
                        :class="{ 'border-red-500': phoneError, 'border-gray-300': !phoneError }"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        required />
                    <p x-show="phoneError" class="text-red-500 text-xs mt-1" x-text="phoneError"></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4" x-data="emailPhoneValidation()">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Orang Tua</label>
                        <input type="text" name="nama_orangtua"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No. Orang Tua</label>
                        {{-- <input type="number" name="nomor_hp_orangtua"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required> --}}
                        <input type="number" id="phone" name="nomor_hp_orangtua" x-model="phone"
                            @input="validatePhone()"
                            :class="{ 'border-red-500': phoneError, 'border-gray-300': !phoneError }"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required />
                        <p x-show="phoneError" class="text-red-500 text-xs mt-1" x-text="phoneError"></p>
                    </div>
                </div>

                <div x-data="prodiSelector()">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div class="mt-4">
                            <label for="first_choice" class="block mb-2 text-sm font-medium text-gray-900">
                                Prodi Pilihan Pertama
                            </label>
                            <select id="first_choice" name="prodi1_id" x-model="selectedFirstChoice"
                                @change="filterSecondChoices()"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="" disabled x-bind:selected="selectedFirstChoice === null">
                                    Pilih</option>
                                @foreach ($prodis as $prodi)
                                    <option value="{{ $prodi->code }}">{{ $prodi->name }}</option>
                                @endforeach
                            </select>
                            @error('prodi1_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mt-4">
                            <label for="second_choice" class="block mb-2 text-sm font-medium text-gray-900">
                                Prodi Pilihan Kedua
                            </label>
                            <select id="second_choice" name="prodi2_id" x-model="selectedSecondChoice"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="" disabled x-bind:selected="selectedSecondChoice === null">
                                    Pilih</option>
                                <template x-for="prodi in filteredProdis" :key="prodi.code">
                                    <option :value="prodi.code" x-text="prodi.name"></option>
                                </template>
                            </select>
                            @error('prodi2_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700">Apakah Anda Menerima PIP?</label>
                    <div class="flex gap-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="pip" value="yes" x-model="pip"
                                class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2">Ya</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="pip" value="no" x-model="pip"
                                class="text-blue-600 focus:ring-blue-500">
                            <span class="ml-2">Tidak</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4" x-show="pip === 'yes'">
                    <label class="block text-sm font-medium text-gray-700">No. KIP</label>
                    <input type="text" name="no_kip"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg shadow-md focus:ring-4 focus:ring-blue-300">
                        Daftar Sekarang
                    </button>
                </div>
            </form>
        </div>

        {{-- Right Column --}}
        <div class="p-8 bg-white rounded-lg sm:col-span-3 grid gap-4">
            <h2 class="font-bold text-xl">Alur Pendaftaran Mahasiswa KIP-K</h2>
            <hr class="border-slate-300 my-4">
            <div class="overflow-hidden sm:rounded-md">
                <ul>
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex gap-4">
                            <p class="font-bold text-2xl text-blue-600">1</p>
                            <p class="text-sm font-medium text-gray-900">Calon mahasiswa (Camaba KIP) mengakses website
                                pmb.bim.ac.id/pendaftaran-kip untuk melakukan pendaftaran.</p>
                        </div>
                    </li>
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex gap-4">
                            <p class="font-bold text-2xl text-blue-600">2</p>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Mengisi formulir pendaftaran dengan data
                                    berikut:</p>
                                <ul class="mt-2 ml-4 list-disc text-sm text-gray-600">
                                    <li>Nama Lengkap</li>
                                    <li>Jenis Kelamin</li>
                                    <li>Tempat & Tanggal Lahir</li>
                                    <li>NISN</li>
                                    <li>Email & Nomor WhatsApp</li>
                                    <li>Asal Sekolah</li>
                                    <li>Nama & Nomor Orang Tua</li>
                                    <li>Pilihan Program Studi</li>
                                    <li>Status penerima PIP (jika iya, mengisi data tambahan)</li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex gap-4">
                            <p class="font-bold text-2xl text-blue-600">3</p>
                            <p class="text-sm font-medium text-gray-900">Setelah mengirimkan formulir, calon mahasiswa
                                akan menerima email berisi password untuk login ke akun.</p>
                        </div>
                    </li>
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex gap-4">
                            <p class="font-bold text-2xl text-blue-600">4</p>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Mengunggah dokumen pendukung, termasuk:
                                </p>
                                <ul class="mt-2 ml-4 list-disc text-sm text-gray-600">
                                    <li>Ijazah SMA/SMK atau Surat Keterangan Lulus</li>
                                    <li>Nilai rata-rata rapor</li>
                                    <li>Pekerjaan & Penghasilan Orang Tua</li>
                                    <li>Alamat Domisili</li>
                                    <li>Bukti follow akun IG BIM</li>
                                    <li>Pas Foto terbaru</li>
                                    <li>Referensi/Kontak Darurat</li>
                                    <li>Alasan mengapa layak mendapatkan beasiswa</li>
                                </ul>
                            </div>
                        </div>
                    </li>
                    <li class="px-4 py-4 sm:px-6">
                        <div class="flex gap-4">
                            <p class="font-bold text-2xl text-blue-600">5</p>
                            <p class="text-sm font-medium text-gray-900">Admin akan melakukan konfirmasi penerimaan.
                                Hasil seleksi akan dikirimkan melalui email. Jika diterima, calon mahasiswa wajib
                                melakukan
                                daftar ulang.</p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

    </div>

    @include('layouts.footer')
    @include('layouts.credit')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const programDropdown = document.getElementById('program');
            const firstChoiceDropdown = document.getElementById('first_choice');
            const secondChoiceDropdown = document.getElementById('second_choice');

            const updateProdiOptions = () => {
                const selectedProgram = programDropdown.value;
                const allowedCodes = ['BD', 'KW'];

                const updateDropdown = (dropdown) => {
                    const options = dropdown.querySelectorAll('option[data-code]');
                    options.forEach(option => {
                        if (selectedProgram == 2 || selectedProgram == 3) {
                            option.style.display = allowedCodes.includes(option.getAttribute(
                                'data-code')) ? 'block' : 'none';
                        } else {
                            option.style.display = 'block';
                        }
                    });
                };

                updateDropdown(firstChoiceDropdown);
                updateDropdown(secondChoiceDropdown);
            };

            programDropdown.addEventListener('change', updateProdiOptions);
        });
    </script>

    <script>
        function promoValidation() {
            return {
                promoCode: '',
                message: '',
                valid: true,
                checkPromo() {
                    if (this.promoCode.length > 0) {
                        this.message = 'Memeriksa kode promo...';
                        fetch("{{ route('promo.validate') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    promo_code: this.promoCode
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                this.valid = data.status === 'valid';
                                this.message = data.message;
                            })
                            .catch(error => {
                                this.valid = false;
                                this.message = 'Kode promo tidak valid atau sudah kedaluwarsa.';
                            });
                    } else {
                        this.message = '';
                        this.valid = true;
                    }
                }
            };
        }

        function formHandler() {
            return {
                selectedProgram: null,
                jalurs: @json($jalurs),
                programs: @json($programs),
                get filteredJalur() {
                    const regularProgram = this.programs.find(program => program.name === 'Regular Class');
                    if (this.selectedProgram == (regularProgram ? regularProgram.id : null)) {
                        return this.jalurs.filter(jalur => ['Beasiswa Parsial', 'Beasiswa Parsial', 'Umum/Reguler']
                            .includes(jalur.name)
                        );
                    } else if (this.selectedProgram == 2 || this.selectedProgram == 3) {
                        return this.jalurs.filter(jalur => jalur.name === 'Umum/Reguler');
                    }
                },
                selectedJalur: null,
            };
        }

        function prodiSelector() {
            return {
                // Data yang diterima dari server
                allProdis: @json($prodis),
                selectedFirstChoice: null,
                selectedSecondChoice: null,
                filteredProdis: [],

                // Inisialisasi
                init() {
                    this.filteredProdis = this.allProdis; // Awalnya semua opsi tersedia
                },

                filterSecondChoices() {
                    if (this.selectedFirstChoice) {
                        // Hanya filter prodi yang berbeda dengan pilihan pertama
                        this.filteredProdis = this.allProdis.filter(
                            prodi => prodi.code !== this.selectedFirstChoice
                        );

                        // Pastikan pilihan kedua tetap valid setelah filtering
                        if (!this.filteredProdis.some(prodi => prodi.code === this.selectedSecondChoice)) {
                            this.selectedSecondChoice = null;
                        }
                    } else {
                        this.filteredProdis = this.allProdis;
                    }
                }
            }
        }

        function emailPhoneValidation() {
            return {
                email: '',
                phone: '',
                emailError: '',
                phoneError: '',

                validateEmail() {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.email)) {
                        this.emailError = 'Email tidak valid.';
                    } else {
                        this.emailError = '';
                    }
                },

                validatePhone() {
                    const phoneRegex = /^[0-9]{10,13}$/;
                    if (!phoneRegex.test(this.phone)) {
                        this.phoneError = 'Nomor telepon harus terdiri dari 10-13 digit.';
                    } else {
                        this.phoneError = '';
                    }
                }
            };
        }
    </script>

</body>

</html>
