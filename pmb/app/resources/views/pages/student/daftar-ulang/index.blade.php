<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Daftar Ulang - {{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .terms-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
            background-color: #f8fafc;
        }

        .terms-section {
            margin-bottom: 1.5rem;
        }

        .terms-section h3 {
            color: #1e40af;
            margin-bottom: 0.5rem;
        }

        .checkbox-container {
            display: flex;
            align-items: flex-start;
            margin: 1rem 0;
        }

        .checkbox-container input[type="checkbox"] {
            margin-top: 0.3rem;
            margin-right: 0.5rem;
        }

        #registerBtn:disabled {
            background-color: #94a3b8;
            cursor: not-allowed;
        }

        .progress-bar {
            height: 4px;
            width: 100%;
            background-color: #e2e8f0;
            margin-bottom: 1rem;
            border-radius: 2px;
        }

        .progress-fill {
            height: 100%;
            background-color: #3b82f6;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .read-indicator {
            font-size: 0.875rem;
            color: #64748b;
            text-align: right;
            margin-bottom: 0.5rem;
        }
    </style>
</head>

<body>
    @include('layouts.navbar')
    @include('layouts.header')

    <div class="bg-slate-100 px-4 py-6 sm:px-32 flex flex-col gap-8 text-slate-700 ">

        <div class="bg-blue-600 text-xl p-4 rounded-lg">
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
        <div class="p-4 bg-white rounded-lg flex flex-col gap-8">
            <h2 class="font-bold text-xl text-green-600">Selamat! Anda Telah Diterima menjadi Mahasiswa Baru BIM
            </h2>
            <hr class="border-slate-300">
            <div class="flex flex-col gap-8">
                <p>Langkah selanjutnya adalah melakukan registrasi ulang dengan melengkapi data dan
                    berkas administrasi berikut:</p>

                <div class="overflow-hidden sm:rounded-md">
                    <ul>
                        <li class="px-4 pb-4 sm:px-6">
                            <div class="flex gap-4">
                                <p class="font-bold text-2xl text-blue-600">1</p>
                                <div>
                                    <p class="font-medium text-gray-900">Mengisi Formulir Biodata:
                                    </p>
                                    <ul class="mt-2 ml-4 list-disc text-gray-600">
                                        <li>Nama Lengkap</li>
                                        <li>Nomor HP</li>
                                        <li>Alamat Lengkap</li>
                                        <li>Tanggal Lahir</li>
                                        {{-- <li>Nomor Induk Kependudukan</li> --}}
                                        <li>Nama Orang Tua/Wali</li>
                                        <li>Nomor HP Orang Tua/Wali</li>
                                        <li>Alamat Orang Tua/Wali</li>
                                        {{-- <li>NIK Orang Tua/Wali</li> --}}
                                        {{-- <li>Hubungan</li> --}}
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex gap-4">
                                <p class="font-bold text-2xl text-blue-600">2</p>
                                <div>
                                    <p class="font-medium text-gray-900">Berkas Administrasi:
                                    </p>
                                    <ul class="mt-2 ml-4 list-disc text-gray-600">
                                        <li>Pas Foto Berwarna</li>
                                        <li>Scan KTP/Paspor</li>
                                        <li>Scan Kartu Ijazah</li>
                                        <li>Scan Surat Pernyataan Orang Tua (template)</li>
                                        <li>Scan Surat Pernyataan Diri (template)</li>
                                    </ul>
                                </div>
                            </div>
                        </li>
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex gap-4">
                                <p class="font-bold text-2xl text-blue-600">3</p>
                                <div>
                                    <p class="font-medium text-gray-900">Daftar Ulang:
                                    </p>
                                    <p class="text-sm text-gray-900">Untuk menyelesaikan proses daftar ulang, harap
                                        melakukan pembayaran sesuai dengan jurusan dan program kelas yang dipilih.
                                        Invoice pembayaran dapat dilihat pada halaman berikutnya.
                                    </p>
                                    <p class="text-gray-900 pt-4"><strong>Catatan:</strong> Untuk semester 1, SPP harus
                                        dibayarkan penuh di awal.
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="px-4 sm:px-6 hidden md:block">
                    <p class="font-bold text-xl text-center mb-6">Rincian Nominal:</p>

                    <!-- Regular Class -->
                    <h3 class="text-xl font-semibold mb-4 text-blue-600 text-center">Regular Class</h3>
                    <div class="overflow-x-auto rounded-lg shadow mb-8">
                        <table class="w-full bg-white border border-gray-300 min-w-[500px]">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">Program Studi
                                    </th>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">DPP (Dana
                                        Pembangunan & Pengembangan)</th>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">SPP (Monthly)
                                    </th>
                                    <th class="py-3 px-4 bg-gray-200 border-b border-gray-300 text-left">SPP (Per
                                        Semester)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Business Digital</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp7.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp1.500.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp9.000.000</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Entrepreneurship</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp6.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp1.350.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp8.100.000</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Food Technology</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp6.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp1.200.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp7.200.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- International Class -->
                    <h3 class="text-xl font-semibold mb-4 text-blue-600 text-center">International Class</h3>
                    <div class="overflow-x-auto rounded-lg shadow mb-8">
                        <table class="w-full bg-white border border-gray-300 min-w-[500px]">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">Program Studi
                                    </th>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">DPP (Dana
                                        Pembangunan & Pengembangan)</th>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">SPP (Monthly)
                                    </th>
                                    <th class="py-3 px-4 bg-gray-200 border-b border-gray-300 text-left">SPP (Per
                                        Semester)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Business Digital</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp9.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp2.500.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp15.000.000</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Entrepreneurship</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp8.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp2.100.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp12.600.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Executive Class -->
                    <h3 class="text-xl font-semibold mb-4 text-blue-600 text-center">Executive Class</h3>
                    <div class="overflow-x-auto rounded-lg shadow mb-8">
                        <table class="w-full bg-white border border-gray-300 min-w-[500px]">
                            <thead>
                                <tr>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">Program Studi
                                    </th>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">DPP (Dana
                                        Pembangunan & Pengembangan)</th>
                                    <th class="py-3 px-4 bg-blue-100 border-b border-gray-300 text-left">SPP (Monthly)
                                    </th>
                                    <th class="py-3 px-4 bg-gray-200 border-b border-gray-300 text-left">SPP (Per
                                        Semester)
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Business Digital</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp9.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp1.900.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp11.400.000</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Entrepreneurship</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp7.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp1.800.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp10.800.000</td>
                                </tr>
                                <tr>
                                    <td class="py-3 px-4 border-b border-gray-300">Food Technology</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp7.000.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp1.500.000</td>
                                    <td class="py-3 px-4 border-b border-gray-300">Rp9.000.000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Alternatif Mobile-Friendly Cards -->
                <div class="block md:hidden">
                    <p class="font-bold text-xl text-center mb-6">Rincian Nominal:</p>

                    <!-- Regular Class Cards -->
                    <h3 class="text-xl font-semibold mb-4 text-blue-600 text-center">Regular Class</h3>
                    <div class="grid grid-cols-1 gap-4 mb-8">
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Business Digital</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp7.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp1.500.000</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Entrepreneurship</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp6.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp1.350.000</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Food Technology</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp6.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp1.200.000</p>
                        </div>
                    </div>

                    <!-- International Class Cards -->
                    <h3 class="text-xl font-semibold mb-4 text-blue-600 text-center">International Class</h3>
                    <div class="grid grid-cols-1 gap-4 mb-8">
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Business Digital</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp9.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp2.500.000
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Entrepreneurship</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp8.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp2.100.000</p>
                        </div>
                    </div>

                    <!-- Executive Class Cards -->
                    <h3 class="text-xl font-semibold mb-4 text-blue-600 text-center">Executive Class</h3>
                    <div class="grid grid-cols-1 gap-4 mb-8">
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Business Digital</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp9.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp1.900.000</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Entrepreneurship</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp7.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp1.800.000</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow border border-gray-200">
                            <h4 class="font-bold text-lg text-blue-700 mb-2">Food Technology</h4>
                            <p class="text-gray-700"><span class="font-semibold">DPP:</span> Rp7.000.000</p>
                            <p class="text-gray-700"><span class="font-semibold">SPP (Monthly):</span> Rp1.500.000</p>
                        </div>
                    </div>
                </div>

                <!-- Syarat dan Ketentuan Section -->
                <div class="px-4 sm:px-6 pt-4">
                    <h2 class="font-bold text-xl text-blue-800 mb-4">Syarat dan Ketentuan Daftar Ulang</h2>
                    <p class="text-gray-700 mb-4">Sebelum melanjutkan proses daftar ulang, harap baca seluruh Syarat
                        dan
                        Ketentuan berikut dengan seksama:</p>

                    <div class="read-indicator" id="readIndicator">0% dibaca</div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                    </div>

                    <div class="terms-container" id="termsContainer">
                        <div class="terms-section">
                            <h3>1. Pembayaran Biaya Daftar Ulang</h3>
                            <p>Calon Mahasiswa Baru (CAMABA) BIM University yang telah dinyatakan diterima wajib
                                melakukan pembayaran biaya daftar ulang sesuai dengan ketentuan yang berlaku.</p>
                        </div>

                        <div class="terms-section">
                            <h3>2. Ketentuan Pengembalian Dana</h3>
                            <p>Segala bentuk biaya pendaftaran ulang yang telah dibayarkan tidak dapat dikembalikan dengan alasan apapun. Termasuk pengunduran diri, tidak melanjutkan proses kuliah, atau alasan pribadi lainnya.</p>
                        </div>

                        <div class="terms-section">
                            <h3>3. Konsekuensi Pengunduran Diri</h3>
                            <p>Apabila CAMABA memutuskan untuk mengundurkan diri setelah melakukan pembayaran, maka
                                pembayaran yang telah dilakukan dianggap hangus dan menjadi hak penuh BIM University.
                            </p>
                        </div>

                        <div class="terms-section">
                            <h3>4. Pernyataan Persetujuan</h3>
                            <p>Dengan melakukan pembayaran daftar ulang dan mengumpulkan berkas yang diperlukan, CAMABA
                                dianggap telah membaca, memahami, dan menyetujui seluruh syarat dan ketentuan yang
                                berlaku di BIM University.</p>
                        </div>
                    </div>

                    <div class="checkbox-container">
                        <input type="checkbox" id="agreeTerms" name="agreeTerms">
                        <label for="agreeTerms" class="text-gray-700">Saya telah membaca dan memahami seluruh Syarat
                            dan Ketentuan di atas dan menyetujui semua ketentuan yang berlaku.</label>
                    </div>
                </div>

                <div class="flex justify-center pb-9">
                    <button id="registerBtn" disabled
                        class="bg-blue-600 hover:bg-blue-800 text-white font-bold py-2 px-4 rounded-lg transition duration-200 ease-in-out transform hover:scale-105">
                        Daftar Ulang Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('layouts.credit')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const termsContainer = document.getElementById('termsContainer');
            const agreeCheckbox = document.getElementById('agreeTerms');
            const registerBtn = document.getElementById('registerBtn');
            const progressFill = document.getElementById('progressFill');
            const readIndicator = document.getElementById('readIndicator');

            let termsRead = false;

            // Fungsi untuk mengecek apakah user telah membaca sampai bawah
            function checkIfScrolledToBottom() {
                const scrollHeight = termsContainer.scrollHeight;
                const scrollTop = termsContainer.scrollTop;
                const clientHeight = termsContainer.clientHeight;

                // Hitung persentase scroll
                const scrollPercentage = Math.round((scrollTop + clientHeight) / scrollHeight * 100);

                // Update progress bar
                progressFill.style.width = Math.min(scrollPercentage, 100) + '%';
                readIndicator.textContent = Math.min(scrollPercentage, 100) + '% dibaca';

                // Jika sudah mencapai 95% dianggap sudah membaca
                if (scrollPercentage >= 95) {
                    termsRead = true;
                    readIndicator.textContent = '100% dibaca - Silakan centang persetujuan';
                    readIndicator.style.color = '#16a34a';
                    progressFill.style.backgroundColor = '#16a34a';
                }

                updateButtonState();
            }

            // Fungsi untuk update state tombol
            function updateButtonState() {
                if (termsRead && agreeCheckbox.checked) {
                    registerBtn.disabled = false;
                } else {
                    registerBtn.disabled = true;
                }
            }

            // Event listener untuk scroll
            termsContainer.addEventListener('scroll', checkIfScrolledToBottom);

            // Event listener untuk checkbox
            agreeCheckbox.addEventListener('change', updateButtonState);

            // Event listener untuk tombol daftar ulang
            registerBtn.addEventListener('click', function() {
                window.location.href = "{{ route('login') }}";
            });
        });
    </script>
</body>

</html>
