<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data dan Kelengkapan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols gap-6">
                <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                        {{ __('Kelengkapan Berkas ' . $student->name) }}
                    </div>
                    <a href="{{ route('admrektorat.dashboard') }}"
                        class="border border-gray-500 hover:border-gray-700 rounded-lg px-2 py-1 flex items-center w-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7M3 12h18" />
                        </svg>
                        Kembali
                    </a>
                    <div class="mt-3 bg-blue-100 p-2 rounded-lg flex gap-2 mb-4 text-blue-600 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                        </svg>
                        <p class="text-xs">Ukuran berkas maksimal 2MB. Jenis file yang diperbolehkan: PDF, JPG, JPEG,
                            PNG</p>
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
                    <form action="{{ route('admrektorat.document.store') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input name="student" value="{{ $student->id }}" class="hidden" type="number" />
                        <input name="user" value="{{ $student->user->id }}" class="hidden" type="number" />
                        <div class="flex flex-col mb-4">
                            <p class="font-medium text-gray-700">Slip Transfer Biaya Pendaftaran</p>
                            @if ($student->studentDocument && $student->studentDocument->slip_transfer)
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument->slip_transfer }}">
                                    <div class="file-upload">
                                        <input name="slip_transfer"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200"
                                            data-default-file="{{ $student->studentDocument->slip_transfer ? asset('storage/' . $student->studentDocument->slip_transfer) : '' }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('slip_transfer')" />
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols items-center gap-4"
                                    id="file-container-{{ $student->studentDocument ? $student->studentDocument->slip_transfer : '' }}">
                                    <div class="file-upload">
                                        <input name="slip_transfer"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200" data-default-file="" />
                                        <x-input-error class="mt-2" :messages="$errors->get('slip_transfer')" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col  mb-4">
                            <p class="font-medium text-gray-700">Scan KTP</p>
                            @if ($student->studentDocument && $student->studentDocument->ktp)
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument->ktp }}">
                                    <div class="file-upload">
                                        <input name="ktp"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200"
                                            data-default-file="{{ $student->studentDocument->ktp ? asset('storage/' . $student->studentDocument->ktp) : '' }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('ktp')" />
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument ? $student->studentDocument->ktp : '' }}">
                                    <div class="file-upload">
                                        <input name="ktp"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200" data-default-file="" />
                                        <x-input-error class="mt-2" :messages="$errors->get('ktp')" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col  mb-4">
                            <p class="font-medium text-gray-700">Scan Kartu Keluarga</p>
                            @if ($student->studentDocument && $student->studentDocument->kartu_keluarga)
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument->kartu_keluarga }}">
                                    <div class="file-upload">
                                        <input name="kartu_keluarga"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200"
                                            data-default-file="{{ $student->studentDocument->kartu_keluarga ? asset('storage/' . $student->studentDocument->kartu_keluarga) : '' }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('kartu_keluarga')" />
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument ? $student->studentDocument->kartu_keluarga : '' }}">
                                    <div class="file-upload">
                                        <input name="kartu_keluarga"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200" data-default-file="" />
                                        <x-input-error class="mt-2" :messages="$errors->get('kartu_keluarga')" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col  mb-4">
                            <p class="font-medium text-gray-700">Scan Akta Kelahiran</p>
                            @if ($student->studentDocument && $student->studentDocument->akta_lahir)
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument->akta_lahir }}">
                                    <div class="file-upload">
                                        <input name="akta_lahir"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200"
                                            data-default-file="{{ $student->studentDocument->akta_lahir ? asset('storage/' . $student->studentDocument->akta_lahir) : '' }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('akta_lahir')" />
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument ? $student->studentDocument->akta_lahir : '' }}">
                                    <div class="file-upload">
                                        <input name="akta_lahir"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200" data-default-file="" />
                                        <x-input-error class="mt-2" :messages="$errors->get('akta_lahir')" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="flex flex-col  mb-4">
                            <p class="font-medium text-gray-700">Scan Ijazah</p>
                            @if ($student->studentDocument && $student->studentDocument->ijazah)
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument->ijazah }}">
                                    <div class="file-upload">
                                        <input name="ijazah"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200"
                                            data-default-file="{{ $student->studentDocument->ijazah ? asset('storage/' . $student->studentDocument->ijazah) : '' }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('ijazah')" />
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument ? $student->studentDocument->ijazah : '' }}">
                                    <div class="file-upload">
                                        <input name="ijazah"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200" data-default-file="" />
                                        <x-input-error class="mt-2" :messages="$errors->get('ijazah')" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        {{-- <div class="flex flex-col  mb-4">
                            <p class="font-medium text-gray-700">Scan Sertifikat</p>
                            @if ($student->studentDocument && $student->studentDocument->prestasi)
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument->prestasi }}">
                                    <div class="file-upload">
                                        <input name="prestasi"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200"
                                            data-default-file="{{ $student->studentDocument->prestasi ? asset('storage/' . $student->studentDocument->prestasi) : '' }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('prestasi')" />
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument ? $student->studentDocument->prestasi : '' }}">
                                    <div class="file-upload">
                                        <input name="prestasi"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200" data-default-file="" />
                                        <x-input-error class="mt-2" :messages="$errors->get('prestasi')" />
                                    </div>
                                </div>
                            @endif
                        </div> --}}
                        <div class="flex flex-col  mb-4">
                            <p class="font-medium text-gray-700">Scan Pas Foto</p>
                            @if ($student->studentDocument && $student->studentDocument->pas_foto)
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument->pas_foto }}">
                                    <div class="file-upload">
                                        <input name="pas_foto"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200"
                                            data-default-file="{{ $student->studentDocument->pas_foto ? asset('storage/' . $student->studentDocument->pas_foto) : '' }}" />
                                        <x-input-error class="mt-2" :messages="$errors->get('pas_foto')" />
                                    </div>
                                </div>
                            @else
                                <div class="grid grid-cols gap-4"
                                    id="file-container-{{ $student->studentDocument ? $student->studentDocument->pas_foto : '' }}">
                                    <div class="file-upload">
                                        <input name="pas_foto"
                                            class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                            type="file" data-height="200" data-default-file="" />
                                        <x-input-error class="mt-2" :messages="$errors->get('image')" />
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if ($student->user->hasPermissionTo('prestasi-access') || $student->user->hasPermissionTo('reguler-access'))
                            <div class="flex flex-col  mb-4">
                                <p class="font-medium text-gray-700">Rapot Semester 1-6</p>
                                @if ($student->studentDocument && $student->studentDocument->nilai_rapot)
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument->nilai_rapot }}">
                                        <div class="file-upload">
                                            <input name="nilai_rapot"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200"
                                                data-default-file="{{ $student->studentDocument->nilai_rapot ? asset('storage/' . $student->studentDocument->nilai_rapot) : '' }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('nilai_rapot')" />
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument ? $student->studentDocument->nilai_rapot : '' }}">
                                        <div class="file-upload">
                                            <input name="nilai_rapot"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200" data-default-file="" />
                                            <x-input-error class="mt-2" :messages="$errors->get('nilai_rapot')" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        @if ($student->user->hasPermissionTo('beasiswa-access'))
                            <div class="flex flex-col  mb-4">
                                <p class="font-medium text-gray-700">Surat Rekomendasi dari Sekolah</p>
                                @if ($student->studentDocument && $student->studentDocument->surat_rekomendasi)
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument->surat_rekomendasi }}">
                                        <div class="file-upload">
                                            <input name="surat_rekomendasi"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200"
                                                data-default-file="{{ $student->studentDocument->surat_rekomendasi ? asset('storage/' . $student->studentDocument->surat_rekomendasi) : '' }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('surat_rekomendasi')" />
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument ? $student->studentDocument->surat_rekomendasi : '' }}">
                                        <div class="file-upload">
                                            <input name="surat_rekomendasi"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200" data-default-file="" />
                                            <x-input-error class="mt-2" :messages="$errors->get('surat_rekomendasi')" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col  mb-4">
                                <p class="font-medium text-gray-700">CV/Resume/Portfolio</p>
                                @if ($student->studentDocument && $student->studentDocument->cv)
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument->cv }}">
                                        <div class="file-upload">
                                            <input name="cv"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200"
                                                data-default-file="{{ $student->studentDocument->cv ? asset('storage/' . $student->studentDocument->cv) : '' }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('cv')" />
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument ? $student->studentDocument->cv : '' }}">
                                        <div class="file-upload">
                                            <input name="cv"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200" data-default-file="" />
                                            <x-input-error class="mt-2" :messages="$errors->get('cv')" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col  mb-4">
                                <p class="font-medium text-gray-700">Pengumpulan Esai</p>
                                @if ($student->studentDocument && $student->studentDocument->esai)
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument->esai }}">
                                        <div class="file-upload">
                                            <input name="esai"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200"
                                                data-default-file="{{ $student->studentDocument->esai ? asset('storage/' . $student->studentDocument->esai) : '' }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('esai')" />
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ $student->studentDocument ? $student->studentDocument->esai : '' }}">
                                        <div class="file-upload">
                                            <input name="esai"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200" data-default-file="" />
                                            <x-input-error class="mt-2" :messages="$errors->get('esai')" />
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col mb-4">
                                <p class="font-medium text-gray-700">Nilai Rapot Semester Akhir</p>
                                @if ($student && ($rapot = $student->getMedia('kelengkapanBerkas')->firstWhere('name', 'rapot_sems_akhir')))
                                    <div class="grid grid-cols gap-4" id="file-container-{{ $rapot->getUrl() }}">
                                        <div class="file-upload">
                                            <input name="rapot_sems_akhir"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200"
                                                data-default-file="{{ $rapot->getUrl() ? $rapot->getUrl() : '' }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('rapot_sems_akhir')" />
                                        </div>
                                    </div>
                                @else
                                    <div class="grid grid-cols gap-4"
                                        id="file-container-{{ '' }}">
                                        <div class="file-upload">
                                            <input name="rapot_sems_akhir"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200" data-default-file="" />
                                            <x-input-error class="mt-2" :messages="$errors->get('rapot_sems_akhir')" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col mb-4">
                                <p class="font-medium text-gray-700">Dokumentasi Tempat Tinggal</p>
                                @if ($student && ($dokTemp = $student->getMedia('kelengkapanBerkas')->firstWhere('name', 'dok_tempat_tinggal')))
                                    <div class="grid grid-cols gap-4" id="file-container-{{ $dokTemp->getUrl() }}">
                                        <div class="file-upload">
                                            <input name="dok_tempat_tinggal"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200"
                                                data-default-file="{{ $dokTemp->getUrl() ? $dokTemp->getUrl() : '' }}" />
                                            <x-input-error class="mt-2" :messages="$errors->get('dok_tempat_tinggal')" />
                                        </div>
                                    </div>
                                @else
                                     <div class="grid grid-cols gap-4"
                                        id="file-container-{{ '' }}">
                                        <div class="file-upload">
                                            <input name="rapot_sems_akhir"
                                                class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                                type="file" data-height="200" data-default-file="" />
                                            <x-input-error class="mt-2" :messages="$errors->get('dok_tempat_tinggal')" />
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                        <div class="mt-4">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('layouts.credit')
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
        <!-- Tambahkan CSS Dropify -->
        <link href="{{ asset('dropify/dist/css/dropify.css') }}" rel="stylesheet">

        <!-- Tambahkan JS Dropify -->
        <script src="{{ asset('dropify/dist/js/dropify.js') }}"></script>

        <script>
            $('.dropify').dropify();
            $('.file-icon').text('Click here to upload ');
        </script>
    @endpush
</x-app-layout>
