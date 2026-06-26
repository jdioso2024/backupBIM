<div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="font-semibold text-xl text-gray-800 leading-tight mb-4">
        {{ __('Kelengkapan Berkas') }}
    </div>
    <div class="bg-blue-100 p-2 rounded-lg flex gap-2 mb-4 text-blue-600 items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="size-4">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <p class="text-xs">Ukuran berkas maksimal 2MB. Jenis file yang diperbolehkan: PDF, JPG, JPEG,
            PNG</p>
    </div>
    {{-- @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Terjadi Kesalahan!</strong>
            <ul class="list-disc pl-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Berhasil!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Gagal!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif --}}
    <form action="{{ route('document.store') }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Form Header -->
        <div class="border-b border-gray-200 pb-4">
            <h3 class="text-lg font-medium text-gray-900">Upload Dokumen Persyaratan</h3>
            <p class="mt-1 text-sm text-gray-500">Silakan unggah dokumen-dokumen yang diperlukan</p>
        </div>

        <!-- Slip Transfer -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Slip Transfer Biaya Pendaftaran</label>
                <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center gap-4 justify-between">
                    <div class="">
                        <input type="file" id="slip_transfer" name="slip_transfer" class="hidden"
                            onchange="updateLabel(this)">
                        <label for="slip_transfer"
                            class="text-sm
                        mr-4 py-2 px-4
                        rounded-md border-0 font-semibold
                        bg-blue-50 text-blue-700
                        hover:bg-blue-100 hover:cursor-pointer">
                            {{ $student->studentDocument && $student->studentDocument->slip_transfer ? 'Update File' : 'Choose File' }}
                        </label>
                        <span id="label-slip_transfer" class="text-sm text-gray-600">
                            @if ($student->studentDocument && $student->studentDocument->slip_transfer)
                                {{ basename($student->studentDocument->slip_transfer) }}
                            @else
                                No file chosen
                            @endif
                        </span>
                    </div>

                    @if ($student->studentDocument && $student->studentDocument->slip_transfer)
                        <a href="{{ asset('storage/' . $student->studentDocument->slip_transfer) }}" target="_blank"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- KTP -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Scan KTP</label>
                <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center gap-4 justify-between">
                    <div class="">
                        <input type="file" id="ktp" name="ktp" class="hidden" onchange="updateLabel(this)">
                        <label for="ktp"
                            class="text-sm
                        mr-4 py-2 px-4
                        rounded-md border-0 font-semibold
                        bg-blue-50 text-blue-700
                        hover:bg-blue-100 hover:cursor-pointer">
                            {{ $student->studentDocument && $student->studentDocument->ktp ? 'Update File' : 'Choose File' }}
                        </label>
                        <span id="label-ktp" class="text-sm text-gray-600">
                            @if ($student->studentDocument && $student->studentDocument->ktp)
                                {{ basename($student->studentDocument->ktp) }}
                            @else
                                No file chosen
                            @endif
                        </span>
                    </div>

                    @if ($student->studentDocument && $student->studentDocument->ktp)
                        <a href="{{ asset('storage/' . $student->studentDocument->ktp) }}" target="_blank"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kartu Keluarga -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Scan Kartu Keluarga</label>
                <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center gap-4 justify-between">
                    <div class="">
                        <input type="file" id="kartu_keluarga" name="kartu_keluarga" class="hidden"
                            onchange="updateLabel(this)">
                        <label for="kartu_keluarga"
                            class="text-sm
                        mr-4 py-2 px-4
                        rounded-md border-0 font-semibold
                        bg-blue-50 text-blue-700
                        hover:bg-blue-100 hover:cursor-pointer">
                            {{ $student->studentDocument && $student->studentDocument->kartu_keluarga ? 'Update File' : 'Choose File' }}
                        </label>
                        <span id="label-kartu_keluarga" class="text-sm text-gray-600">
                            @if ($student->studentDocument && $student->studentDocument->kartu_keluarga)
                                {{ basename($student->studentDocument->kartu_keluarga) }}
                            @else
                                No file chosen
                            @endif
                        </span>
                    </div>

                    @if ($student->studentDocument && $student->studentDocument->kartu_keluarga)
                        <a href="{{ asset('storage/' . $student->studentDocument->kartu_keluarga) }}" target="_blank"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Akta Kelahiran -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Scan Akta Kelahiran</label>
                <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center gap-4 justify-between">
                    <div class="">
                        <input type="file" id="akta_lahir" name="akta_lahir" class="hidden"
                            onchange="updateLabel(this)">
                        <label for="akta_lahir"
                            class="text-sm
                    mr-4 py-2 px-4
                    rounded-md border-0 font-semibold
                    bg-blue-50 text-blue-700
                    hover:bg-blue-100 hover:cursor-pointer">
                            {{ $student->studentDocument && $student->studentDocument->akta_lahir ? 'Update File' : 'Choose File' }}
                        </label>
                        <span id="label-akta_lahir" class="text-sm text-gray-600">
                            @if ($student->studentDocument && $student->studentDocument->akta_lahir)
                                {{ basename($student->studentDocument->akta_lahir) }}
                            @else
                                No file chosen
                            @endif
                        </span>
                    </div>

                    @if ($student->studentDocument && $student->studentDocument->akta_lahir)
                        <a href="{{ asset('storage/' . $student->studentDocument->akta_lahir) }}" target="_blank"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ijazah -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Scan Ijazah</label>
                <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center gap-4 justify-between">
                    <div class="">
                        <input type="file" id="ijazah" name="ijazah" class="hidden"
                            onchange="updateLabel(this)">
                        <label for="ijazah"
                            class="text-sm
                        mr-4 py-2 px-4
                        rounded-md border-0 font-semibold
                        bg-blue-50 text-blue-700
                        hover:bg-blue-100 hover:cursor-pointer">
                            {{ $student->studentDocument && $student->studentDocument->ijazah ? 'Update File' : 'Choose File' }}
                        </label>
                        <span id="label-ijazah" class="text-sm text-gray-600">
                            @if ($student->studentDocument && $student->studentDocument->ijazah)
                                {{ basename($student->studentDocument->ijazah) }}
                            @else
                                No file chosen
                            @endif
                        </span>
                    </div>

                    @if ($student->studentDocument && $student->studentDocument->ijazah)
                        <a href="{{ asset('storage/' . $student->studentDocument->ijazah) }}" target="_blank"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pas Foto -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="sm:col-span-1">
                <label class="block text-sm font-medium text-gray-700">Scan Pas Foto</label>
                <p class="mt-1 text-xs text-gray-500">Format: JPG/PNG (max 2MB)</p>
            </div>
            <div class="sm:col-span-2">
                <div class="flex items-center gap-4 justify-between">
                    <div class="">
                        <input type="file" id="pas_foto" name="pas_foto" class="hidden"
                            onchange="updateLabel(this)">
                        <label for="pas_foto"
                            class="text-sm
                        mr-4 py-2 px-4
                        rounded-md border-0 font-semibold
                        bg-blue-50 text-blue-700
                        hover:bg-blue-100 hover:cursor-pointer">
                            {{ $student->studentDocument && $student->studentDocument->pas_foto ? 'Update File' : 'Choose File' }}
                        </label>
                        <span id="label-pas_foto" class="text-sm text-gray-600">
                            @if ($student->studentDocument && $student->studentDocument->pas_foto)
                                {{ basename($student->studentDocument->pas_foto) }}
                            @else
                                No file chosen
                            @endif
                        </span>
                    </div>

                    @if ($student->studentDocument && $student->studentDocument->pas_foto)
                        <a href="{{ asset('storage/' . $student->studentDocument->pas_foto) }}" target="_blank"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Lihat
                        </a>
                    @endif
                </div>
            </div>
        </div>

        @canany(['prestasi-access', 'reguler-access'])
            <!-- Rapot Semester -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Rapot Semester 1-6</label>
                    <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-4 justify-between">
                        <div class="">
                            <input type="file" id="nilai_rapot" name="nilai_rapot" class="hidden"
                                onchange="updateLabel(this)">
                            <label for="nilai_rapot"
                                class="text-sm
                            mr-4 py-2 px-4
                            rounded-md border-0 font-semibold
                            bg-blue-50 text-blue-700
                            hover:bg-blue-100 hover:cursor-pointer">
                                {{ $student->studentDocument && $student->studentDocument->nilai_rapot ? 'Update File' : 'Choose File' }}
                            </label>
                            <span id="label-nilai_rapot" class="text-sm text-gray-600">
                                @if ($student->studentDocument && $student->studentDocument->nilai_rapot)
                                    {{ basename($student->studentDocument->nilai_rapot) }}
                                @else
                                    No file chosen
                                @endif
                            </span>
                        </div>

                        @if ($student->studentDocument && $student->studentDocument->nilai_rapot)
                            <a href="{{ asset('storage/' . $student->studentDocument->nilai_rapot) }}" target="_blank"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endcanany

        @can('beasiswa-access')
            <!-- Surat Rekomendasi -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Surat Rekomendasi dari Sekolah</label>
                    <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-4 justify-between">
                        <div class="">
                            <input type="file" id="surat_rekomendasi" name="surat_rekomendasi" class="hidden"
                                onchange="updateLabel(this)">
                            <label for="surat_rekomendasi"
                                class="text-sm
                            mr-4 py-2 px-4
                            rounded-md border-0 font-semibold
                            bg-blue-50 text-blue-700
                            hover:bg-blue-100 hover:cursor-pointer">
                                {{ $student->studentDocument && $student->studentDocument->surat_rekomendasi ? 'Update File' : 'Choose File' }}
                            </label>
                            <span id="label-surat_rekomendasi" class="text-sm text-gray-600">
                                @if ($student->studentDocument && $student->studentDocument->surat_rekomendasi)
                                    {{ basename($student->studentDocument->surat_rekomendasi) }}
                                @else
                                    No file chosen
                                @endif
                            </span>
                        </div>

                        @if ($student->studentDocument && $student->studentDocument->surat_rekomendasi)
                            <a href="{{ asset('storage/' . $student->studentDocument->surat_rekomendasi) }}"
                                target="_blank"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- CV/Resume -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">CV/Resume/Portfolio</label>
                    <p class="mt-1 text-xs text-gray-500">Format: PDF (max 2MB)</p>
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-4 justify-between">
                        <div class="">
                            <input type="file" id="cv" name="cv" class="hidden"
                                onchange="updateLabel(this)">
                            <label for="cv"
                                class="text-sm
                            mr-4 py-2 px-4
                            rounded-md border-0 font-semibold
                            bg-blue-50 text-blue-700
                            hover:bg-blue-100 hover:cursor-pointer">
                                {{ $student->studentDocument && $student->studentDocument->cv ? 'Update File' : 'Choose File' }}
                            </label>
                            <span id="label-cv" class="text-sm text-gray-600">
                                @if ($student->studentDocument && $student->studentDocument->cv)
                                    {{ basename($student->studentDocument->cv) }}
                                @else
                                    No file chosen
                                @endif
                            </span>

                        </div>
                        @if ($student->studentDocument && $student->studentDocument->cv)
                            <a href="{{ asset('storage/' . $student->studentDocument->cv) }}" target="_blank"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Esai -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Pengumpulan Esai</label>
                    <p class="mt-1 text-xs text-gray-500">Format: PDF/DOCX (max 2MB)</p>
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-4 justify-between">
                        <div class="">
                            <input type="file" id="esai" name="esai" class="hidden"
                                onchange="updateLabel(this)">
                            <label for="esai"
                                class="text-sm
                            mr-4 py-2 px-4
                            rounded-md border-0 font-semibold
                            bg-blue-50 text-blue-700
                            hover:bg-blue-100 hover:cursor-pointer">
                                {{ $student->studentDocument && $student->studentDocument->esai ? 'Update File' : 'Choose File' }}
                            </label>
                            <span id="label-esai" class="text-sm text-gray-600">
                                @if ($student->studentDocument && $student->studentDocument->esai)
                                    {{ basename($student->studentDocument->esai) }}
                                @else
                                    No file chosen
                                @endif
                            </span>
                        </div>

                        @if ($student->studentDocument && $student->studentDocument->esai)
                            <a href="{{ asset('storage/' . $student->studentDocument->esai) }}" target="_blank"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Rapot Semester Akhir -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-1">
                    <label class="block text-sm font-medium text-gray-700">Nilai Rapot Semester Akhir</label>
                    <p class="mt-1 text-xs text-gray-500">Format: PDF/JPG/PNG (max 2MB)</p>
                </div>
                <div class="sm:col-span-2">
                    <div class="flex items-center gap-4 justify-between">
                        <div class="">
                            <input type="file" id="rapot_sems_akhir" name="rapot_sems_akhir" class="hidden"
                                onchange="updateLabel(this)">
                            <label for="rapot_sems_akhir"
                                class="text-sm
                            mr-4 py-2 px-4
                            rounded-md border-0 font-semibold
                            bg-blue-50 text-blue-700
                            hover:bg-blue-100 hover:cursor-pointer">
                                {{ $student && ($rapot = $student->getMedia('kelengkapanBerkas')->firstWhere('name', 'rapot_sems_akhir')) ? 'Update File' : 'Choose File' }}
                            </label>
                            <span id="label-rapot_sems_akhir" class="text-sm text-gray-600">
                                @if ($student && ($rapot = $student->getMedia('kelengkapanBerkas')->firstWhere('name', 'rapot_sems_akhir')))
                                    {{ $rapot->name }}
                                @else
                                    No file chosen
                                @endif
                            </span>
                        </div>

                        @if ($student && ($rapot = $student->getMedia('kelengkapanBerkas')->firstWhere('name', 'rapot_sems_akhir')))
                            <a href="{{ $rapot->getUrl() }}" target="_blank"
                                class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Lihat
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endcan

        <!-- Submit Button -->
        <div class="pt-6 border-t border-gray-200">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Simpan Dokumen
            </button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        function updateLabel(input) {
            const label = document.getElementById('label-' + input.id);
            if (input.files.length > 0) {
                label.textContent = input.files[0].name;
            }
        }
    </script>
@endpush
