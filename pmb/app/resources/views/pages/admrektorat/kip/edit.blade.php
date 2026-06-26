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
                    <a href="{{ route('admrektorat.kip.kip-show', $student) }}"
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
                    <form action="{{ route('admrektorat.kip.document.store') }}" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        <input name="student" value="{{ $student->id }}" class="hidden" type="number" />
                        <input name="user" value="{{ $student->user->id }}" class="hidden" type="number" />
                        {{-- Bukti Pembayaran --}}
                        <div class="flex flex-col mb-4">
                            <label class="block font-medium text-gray-700">Slip Transfer Biaya Pendaftaran</label>
                            @php
                                $slip_transfer = $student->biodata
                                    ->getMedia('student_document')
                                    ->firstWhere('name', 'slip_transfer');
                            @endphp
                            <div class="grid grid-cols gap-4"
                                id="file-container-{{ $slip_transfer ? $slip_transfer->getUrl() : '' }}">
                                <div class="file-upload">
                                    <input name="slip_transfer"
                                        class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                        type="file" data-height="200"
                                        data-default-file="{{ $slip_transfer ? $slip_transfer->getUrl() : '' }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('slip_transfer')" />
                                </div>
                            </div>
                        </div>
                        {{-- Scan Ijazah --}}
                        <div class="flex flex-col mb-4">
                            <label class="block font-medium text-gray-700">Scan Ijazah SMA/SMK</label>
                            @php
                                $ijazah = $student->biodata->getMedia('student_document')->firstWhere('name', 'ijazah');
                            @endphp
                            <div class="grid grid-cols gap-4"
                                id="file-container-{{ $ijazah ? $ijazah->getUrl() : '' }}">
                                <div class="file-upload">
                                    <input name="ijazah"
                                        class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                        type="file" data-height="200"
                                        data-default-file="{{ $ijazah ? $ijazah->getUrl() : '' }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('ijazah')" />
                                </div>
                            </div>
                        </div>

                        {{-- Rapot Semester 1-6 --}}
                        <div class="flex flex-col mb-4">
                            <label class="block font-medium text-gray-700">Rapot Semester 1-6</label>
                            @php
                                $rapot = $student->biodata
                                    ->getMedia('student_document')
                                    ->firstWhere('name', 'nilai_rapot');
                            @endphp
                            <div class="grid grid-cols gap-4" id="file-container-{{ $rapot ? $rapot->getUrl() : '' }}">
                                <div class="file-upload">
                                    <input name="nilai_rapot"
                                        class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                        type="file" data-height="200"
                                        data-default-file="{{ $rapot ? $rapot->getUrl() : '' }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('nilai_rapot')" />
                                </div>
                            </div>
                        </div>

                        {{-- Scan Pas Foto --}}
                        <div class="flex flex-col mb-4">
                            <label class="block font-medium text-gray-700">Scan Pas Foto</label>
                            @php
                                $pasFoto = $student->biodata
                                    ->getMedia('student_document')
                                    ->firstWhere('name', 'pas_foto');
                            @endphp
                            <div class="grid grid-cols gap-4"
                                id="file-container-{{ $pasFoto ? $pasFoto->getUrl() : '' }}">
                                <div class="file-upload">
                                    <input name="pas_foto"
                                        class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                        type="file" data-height="200"
                                        data-default-file="{{ $pasFoto ? $pasFoto->getUrl() : '' }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('pas_foto')" />
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col mb-4">
                            <label class="block font-medium text-gray-700">Bukti Follow IG BIM</label>
                            @php
                                $follow_ig = $student->biodata
                                    ->getMedia('student_document')
                                    ->firstWhere('name', 'follow_ig');
                            @endphp
                            <div class="grid grid-cols gap-4"
                                id="file-container-{{ $follow_ig ? $follow_ig->getUrl() : '' }}">
                                <div class="file-upload">
                                    <input name="follow_ig"
                                        class="dropify block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                        type="file" data-height="200"
                                        data-default-file="{{ $follow_ig ? $follow_ig->getUrl() : '' }}" />
                                    <x-input-error class="mt-2" :messages="$errors->get('follow_ig')" />
                                </div>
                            </div>
                        </div>

                        {{-- Alamat Domisili --}}
                        <div class="flex flex-col  mb-4">
                            <label class="block font-medium text-gray-700">Alamat Domisili</label>
                            <input type="text" name="address" placeholder="Contoh: Jl. Raya No. 1, Kota"
                                value="{{ old('address', $student->biodata->alamat) }}"
                                class="border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300">
                        </div>

                        {{-- Pekerjaan Orang Tua --}}
                        <div class="flex flex-col  mb-4">
                            <label class="block font-medium text-gray-700">Pekerjaan Orang Tua</label>
                            <input type="text" name="parent_work" placeholder="Contoh: Wiraswasta, Pegawai, dll."
                                value="{{ old('parent_work', $student->biodata->parent_work) }}"
                                class="border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300">
                        </div>

                        {{-- Penghasilan Orang Tua --}}
                        <div class="flex flex-col  mb-4">
                            <label class="block font-medium text-gray-700">Rata-Rata Penghasilan Orang Tua</label>
                            {{-- <input type="text" name="parent_income" placeholder="Penghasilan per bulan (Rp)"
                                value="{{ old('parent_income', $student->biodata->parent_income) }}"
                                class="border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300"> --}}
                            <select name="parent_income"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option readonly>Pilih rata-rata penghasilan orang tua</option>
                                <option value="< 1.000.000"
                                    {{ $student->biodata->parent_income === '< 1.000.000' ? 'selected' : '' }}>
                                    < Rp. 1.000.000</option>
                                <option value="1.000.001 - 3.000.000"
                                    {{ $student->biodata->parent_income === '1.000.001 - 3.000.000' ? 'selected' : '' }}>
                                    Rp. 1.000.001
                                    -
                                    3.000.000</option>
                                <option value="3.000.001 - 5.000.000"
                                    {{ $student->biodata->parent_income === '3.000.001 - 5.000.000' ? 'selected' : '' }}>
                                    Rp. 3.000.001
                                    - 5.000.000</option>
                                <option value="5.000.001 - 7.000.000"
                                    {{ $student->biodata->parent_income === '5.000.001 - 7.000.000' ? 'selected' : '' }}>
                                    Rp. 5.000.001
                                    - 7.000.000</option>
                                <option value="7.000.001 - 10.000.000"
                                    {{ $student->biodata->parent_income === '7.000.001 - 10.000.000' ? 'selected' : '' }}>
                                    Rp. 7.000.001
                                    - 10.000.000</option>
                                <option value=">10.000.000"
                                    {{ $student->biodata->parent_income === '>10.000.000' ? 'selected' : '' }}>
                                    > Rp. 10.000.000</option>
                            </select>
                        </div>

                        {{-- Kontak Darurat --}}
                        <div class="flex flex-col  mb-4">
                            <label class="block font-medium text-gray-700">Referensi/Kontak Darurat</label>
                            <input type="text" name="emergency_contact" placeholder="Nomor kontak alternatif"
                                value="{{ old('emergency_contact', $student->biodata->emergency_contact) }}"
                                class="border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300">
                        </div>

                        {{-- Alasan Mendapatkan Beasiswa --}}
                        <div class="flex flex-col  mb-4">
                            <label class="block font-medium text-gray-700">Alasan Mengapa Layak Mendapatkan
                                Beasiswa</label>
                            <textarea name="reason_scholarship" class="border rounded-lg px-4 py-2 h-40 focus:ring focus:ring-blue-300">{{ old('reason_scholarship', $student->biodata->reason_scholarship) }}</textarea>
                        </div>
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
