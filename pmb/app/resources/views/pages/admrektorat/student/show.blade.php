<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data dan Kelengkapan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('Data Calon Mahasiswa') }}
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Nama Lengkap</p>
                            <input type="text" disabled value="{{ $student->name }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Email</p>
                            <input type="text" disabled value="{{ $student->user->email }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">No HP/WA</p>
                            <input type="text" disabled value="{{ $student->phone_number }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Tgl. Registrasi</p>
                            <input type="text" disabled value="{{ $student->register_at }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Pilihan #1</p>
                            <input type="text" disabled value="{{ $student->prodi1->name ?? '-' }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Pilihan #2</p>
                            <input type="text" disabled value="{{ $student->prodi2->name ?? '-' }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Jalur Pendaftaran</p>
                            <input type="text" disabled value="{{ $student->jalurPendaftaran->name ?? '-' }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Program Pilihan</p>
                            <input type="text" disabled value="{{ $student->program->name ?? '-' }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Status</p>
                            @if ($student->studentDocument)
                                @if ($student->status == 0)
                                    <input type="text" disabled value="Berkas Belum Lengkap"
                                        class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                                @elseif ($student->status == 1)
                                    <input type="text" disabled value="Slip Transfer Sudah dikonfirmasi"
                                        class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                                @elseif ($student->status == 2)
                                    <input type="text" disabled value="Diterima Pilihan #1"
                                        class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                                @elseif ($student->status == 3)
                                    <input type="text" disabled value="Diterima Pilihan #2"
                                        class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                                @elseif ($student->status == 4)
                                    <input type="text" disabled value="Ditolak"
                                        class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                                @elseif ($student->status == 5)
                                    <input type="text" disabled value="Lolos Seleksi Berkas"
                                        class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                                @endif
                            @else
                                <input type="text" disabled value="Belum Mengupload Berkas"
                                    class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Nama Orang Tua</p>
                            <input type="text" disabled value="{{ $student->biodata->nama_orangtua ?? '-' }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Kontak Orang Tua</p>
                            <input type="text" disabled value="{{ $student->biodata->nomor_hp_orangtua ?? '-' }}"
                                class="ml-4 bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 w-2/3">
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('Kelengkapan Berkas') }}
                    </div>
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
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Slip Transfer Biaya Pendaftaran</p>
                        @if ($student->studentDocument && $student->studentDocument->slip_transfer)
                            <a href="{{ asset('storage/' . $student->studentDocument->slip_transfer) }}"
                                target="_blank" class="ml-4 text-blue-500">Lihat File</a>
                        @else
                            <p class="ml-4 text-red-500">Belum Upload</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Scan KTP</p>
                        @if ($student->studentDocument && $student->studentDocument->ktp)
                            <a href="{{ asset('storage/' . $student->studentDocument->ktp) }}" target="_blank"
                                class="ml-4 text-blue-500">Lihat File</a>
                        @else
                            <p class="ml-4 text-red-500">Belum Upload</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Scan Kartu Keluarga</p>
                        @if ($student->studentDocument && $student->studentDocument->kartu_keluarga)
                            <a href="{{ asset('storage/' . $student->studentDocument->kartu_keluarga) }}"
                                target="_blank" class="ml-4 text-blue-500">Lihat File</a>
                        @else
                            <p class="ml-4 text-red-500">Belum Upload</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Scan Akta Kelahiran</p>
                        @if ($student->studentDocument && $student->studentDocument->akta_lahir)
                            <a href="{{ asset('storage/' . $student->studentDocument->akta_lahir) }}" target="_blank"
                                class="ml-4 text-blue-500">Lihat File</a>
                        @else
                            <p class="ml-4 text-red-500">Belum Upload</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Scan Ijazah</p>
                        @if ($student->studentDocument && $student->studentDocument->ijazah)
                            <a href="{{ asset('storage/' . $student->studentDocument->ijazah) }}" target="_blank"
                                class="ml-4 text-blue-500">Lihat File</a>
                        @else
                            <p class="ml-4 text-red-500">Belum Upload</p>
                        @endif
                    </div>
                    {{-- <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Scan Sertifikat</p>
                        @if ($student->studentDocument && $student->studentDocument->prestasi)
                            <a href="{{ asset('storage/' . $student->studentDocument->prestasi) }}" target="_blank"
                                class="ml-4 text-blue-500">Lihat File</a>
                        @else
                            <p class="ml-4 text-red-500">Belum Upload</p>
                        @endif
                    </div> --}}
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Scan Pas Foto</p>
                        @if ($student->studentDocument && $student->studentDocument->pas_foto)
                            <a href="{{ asset('storage/' . $student->studentDocument->pas_foto) }}" target="_blank"
                                class="ml-4 text-blue-500">Lihat File</a>
                        @else
                            <p class="ml-4 text-red-500">Belum Upload</p>
                        @endif
                    </div>
                    @if ($student->user->hasPermissionTo('prestasi-access') || $student->user->hasPermissionTo('reguler-access'))
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Rapot Semester 1-6</p>
                            @if ($student->studentDocument && $student->studentDocument->nilai_rapot)
                                <a href="{{ asset('storage/' . $student->studentDocument->nilai_rapot) }}"
                                    target="_blank" class="ml-4 text-blue-500">Lihat File</a>
                            @else
                                <p class="ml-4 text-red-500">Belum Upload</p>
                            @endif
                        </div>
                    @endif

                    <!-- Cek apakah user yang sedang ditampilkan memiliki izin 'beasiswa-access' -->
                    @if ($student->user->hasPermissionTo('beasiswa-access'))
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Surat Rekomendasi dari Sekolah</p>
                            @if ($student->studentDocument && $student->studentDocument->surat_rekomendasi)
                                <a href="{{ asset('storage/' . $student->studentDocument->surat_rekomendasi) }}"
                                    target="_blank" class="ml-4 text-blue-500">Lihat File</a>
                            @else
                                <p class="ml-4 text-red-500">Belum Upload</p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">CV/Resume/Portfolio</p>
                            @if ($student->studentDocument && $student->studentDocument->cv)
                                <a href="{{ asset('storage/' . $student->studentDocument->cv) }}" target="_blank"
                                    class="ml-4 text-blue-500">Lihat File</a>
                            @else
                                <p class="ml-4 text-red-500">Belum Upload</p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Pengumpulan Esai</p>
                            @if ($student->studentDocument && $student->studentDocument->esai)
                                <a href="{{ asset('storage/' . $student->studentDocument->esai) }}" target="_blank"
                                    class="ml-4 text-blue-500">Lihat File</a>
                            @else
                                <p class="ml-4 text-red-500">Belum Upload</p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Nilai Rapot Semester Akhir</p>
                            @if ($student && ($rapot = $student->getMedia('kelengkapanBerkas')->firstWhere('name', 'rapot_sems_akhir')))
                                <a href="{{ $rapot->getUrl() }}" target="_blank"
                                    class="text-blue-600 hover:underline">Lihat file</a>
                            @else
                                <p class="ml-4 text-red-500">Belum Upload</p>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <p class="font-medium text-gray-700">Dokumentasi Tempat Tinggal</p>
                            @if ($student && ($dokTemp = $student->getMedia('kelengkapanBerkas')->firstWhere('name', 'dok_tempat_tinggal')))
                                <a href="{{ $dokTemp->getUrl() }}" target="_blank"
                                    class="text-blue-600 hover:underline">Lihat file</a>
                            @else
                                <p class="ml-4 text-red-500">Belum Upload</p>
                            @endif
                        </div>

                        @if ($student->status == 5)
                            @if ($student->examData)
                                <div class="py-4">
                                    <p class="text-green-600">Data Ujian Sudah Terkirim</p>
                                    {{-- <button type="button" data-modal-target="authentication-modal"
                                        data-modal-toggle="authentication-modal"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mt-4">Kirim Ulang</button> --}}
                                </div>
                            @else
                                <div class="py-4">
                                    <button type="button" data-modal-target="authentication-modal"
                                        data-modal-toggle="authentication-modal"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Kirim
                                        Email Ujian</button>
                                </div>
                            @endif
                        @endif
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('admrektorat.student.edit', $student) }}"
                            class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-2 px-4 rounded-lg">Edit
                            Berkas</a>
                        @if (session('successDaftarUlang'))
                            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                                role="alert">
                                <strong class="font-bold">Berhasil!</strong>
                                <span class="block sm:inline">{{ session('successDaftarUlang') }}</span>
                            </div>
                        @elseif ($student->status == 2 || $student->status == 3)
                            <a href="{{ route('admrektorat.student.showDocument', $student) }}"
                                class="p-2 bg-blue-600 hover:bg-blue-800 text-white font-medium  rounded-lg">Lihat
                                Berkas Daftar
                                Ulang</a>
                        @elseif ($student->status == 5)
                            <a href="{{ route('admrektorat.student.showDocument', $student) }}"
                                class="p-2 bg-blue-600 hover:bg-blue-800 text-white font-medium  rounded-lg">Lihat
                                Berkas Daftar
                                Ulang</a>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </div>
    @push('modals')
        <!-- Main modal -->
        <div id="authentication-modal" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative p-4 w-full max-w-md max-h-full">
                <!-- Modal content -->
                <div class="relative bg-white rounded-lg shadow">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                        <h3 class="text-xl font-semibold text-gray-900 ">
                            Kirim Data Ujian Jalur Beasiswa
                        </h3>
                        <button type="button"
                            class="end-2.5 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                            data-modal-hide="authentication-modal">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>
                    <!-- Modal body -->
                    <div class="p-4 md:p-5">
                        <form action="{{ route('admrektorat.exam-data.store', $student) }}" class="space-y-4"
                            method="POST">
                            @csrf
                            <div>
                                <label for="date" class="block mb-2 text-sm font-medium text-gray-900">Tanggal
                                    Ujian</label>
                                <input type="date" name="date" id="date"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required />
                            </div>
                            <div>
                                <label for="time" class="block mb-2 text-sm font-medium text-gray-900">Waktu
                                    Ujian</label>
                                <input type="time" name="time" id="time"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    required />
                            </div>
                            <div>
                                <label for="duration" class="block mb-2 text-sm font-medium text-gray-900">Durasi
                                    Ujian</label>
                                <input type="number" name="duration" id="duration"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Durasi (Menit)" required />
                            </div>
                            <div>
                                <label for="url" class="block mb-2 text-sm font-medium text-gray-900">Link
                                    Ujian</label>
                                <input type="text" name="url" id="url"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Url menuju pengerjaan ujian" required />
                            </div>
                            <div>
                                <label for="code" class="block mb-2 text-sm font-medium text-gray-900">Kode Akses
                                    (opsional)</label>
                                <input type="text" name="code" id="code"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                                    placeholder="Kode Akses ujian" />
                            </div>

                            <button type="submit"
                                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Kirim
                                Data</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endpush
</x-app-layout>
