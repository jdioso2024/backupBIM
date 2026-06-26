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
                        {{ __('Berkas Daftar Ulang ' . ($data ? $data->name : $student->name)) }}

                    </div>
                    <a href="{{ route('admrektorat.dashboard') }}"
                        class="border border-gray-500 hover:border-gray-700 rounded-lg px-2 py-1 flex items-center w-fit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-5 h-5 mr-2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7M3 12h18" />
                        </svg>
                        Kembali
                    </a>
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

                    @if ($mediaItems->isNotEmpty())
                        @php
                            $descriptions = [
                                'pas_foto' => 'Pas Foto',
                                'ktp' => 'KTP',
                                'ijazah' => 'Ijazah',
                                'pernyataan_diri' => 'Pernyataan Diri',
                                'pernyataan_ortu' => 'Pernyataan Orang Tua',
                                'keterangan_penghasilan' => 'Keterangan Penghasilan',
                                'bukti_pembayaran' => 'Bukti Pembayaran',
                            ];
                        @endphp
                        @if ($mediaItems->isNotEmpty())
                            @foreach ($mediaItems as $media)
                                <div>
                                    <div class="flex justify-between gap-6 mb-4 dont-download pt-4">
                                        <div class="text-center mt-2">
                                            <p class="text-base font-semibold text-gray-700">
                                                {{ $descriptions[$media->name] ?? 'Dokumen Lainnya' }}
                                            </p>
                                        </div>
                                        <div class="flex gap-x-2">
                                            <p class="font-semibold text-yellow-300 my-auto">Jika file tidak terlihat,
                                                Anda
                                                bisa melakukan
                                                download file</p>
                                            <a href="{{ asset($media->getFullUrl()) }}" download
                                                class="px-4 py-2 bg-gray-400 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 text-center">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                    @if ($media->mime_type == 'image/jpeg' || $media->mime_type == 'image/png')
                                        <div>
                                            <img src="{{ asset($media->getFullUrl()) }}" alt="Image"
                                                class="w-full h-96 object-contain">
                                        </div>
                                    @elseif($media->mime_type == 'application/pdf')
                                        <div>
                                            <iframe src="{{ asset($media->getFullUrl()) }}"
                                                class="w-full object-fill h-96" title="description"></iframe>
                                        </div>
                                    @else
                                        <div>
                                            <p class="text-gray-600">Unsupported file type: {{ $media->mime_type }}</p>
                                        </div>
                                    @endif

                                </div>
                            @endforeach
                        @endif

                    @endif
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
