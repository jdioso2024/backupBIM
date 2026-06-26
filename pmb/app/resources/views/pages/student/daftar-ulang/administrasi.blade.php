<x-app-layout>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="font-semibold text-xl text-gray-800 leading-tight mb-4">
                    {{ __('Daftar Ulang Mahasiswa Baru') }}
                </div>
                <div class="py-4 pb-6 sm:pb-10">
                    <ol
                        class="items-center w-full space-y-4 sm:flex sm:space-x-8 sm:space-y-0 rtl:space-x-reverse justify-center">
                        <li class="flex items-center text-gray-500 dark:text-gray-400 space-x-2.5 rtl:space-x-reverse">
                            <span
                                class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400">1</span>
                            <span>
                                <h3 class="font-medium leading-tight">Biodata</h3>
                                <p class="text-sm">Data diri lengkap</p>
                            </span>
                        </li>
                        <li class="flex items-center text-blue-600 dark:text-blue-500 space-x-2.5 rtl:space-x-reverse">
                            <span
                                class="flex items-center justify-center w-8 h-8 border border-blue-600 rounded-full shrink-0 dark:border-blue-500">2</span>
                            <span>
                                <h3 class="font-medium leading-tight">Administrasi</h3>
                                <p class="text-sm">Berkas dan Pernyataan</p>
                            </span>
                        </li>
                        @if (!$student->user->hasPermissionTo('kip'))
                            <li
                                class="flex items-center text-gray-500 dark:text-gray-400 space-x-2.5 rtl:space-x-reverse">
                                <span
                                    class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400">3</span>
                                <span>
                                    <h3 class="font-medium leading-tight">Pembayaran</h3>
                                    <p class="text-sm">Sesuai jurusan dan program yang dipilih</p>
                                </span>
                            </li>
                        @endif
                    </ol>
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
                <form action="{{ route('student.daftar-ulang.administrasi.store', $student) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Pas Foto -->
                            <div class="flex flex-col">
                                <label for="pas_foto" class="font-medium text-gray-700">Pas Foto Berwarna</label>
                                <input type="file" name="pas_foto"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                @if ($student->biodata && $student->biodata->hasMedia('daftarUlang'))
                                    @foreach ($student->biodata->getMedia('daftarUlang') as $media)
                                        @if ($media->name == 'pas_foto')
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="text-blue-600 hover:underline">Lihat Pas Foto</a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <!-- KTP -->
                            <div class="flex flex-col">
                                <label for="ktp" class="font-medium text-gray-700">Scan KTP/Paspor</label>
                                <input type="file" name="ktp"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                @if ($student->biodata && $student->biodata->hasMedia('daftarUlang'))
                                    @foreach ($student->biodata->getMedia('daftarUlang') as $media)
                                        @if ($media->name == 'ktp')
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="text-blue-600 hover:underline">Lihat KTP</a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <!-- Ijazah -->
                            <div class="flex flex-col">
                                <label for="ijazah" class="font-medium text-gray-700">Scan Ijazah</label>
                                <input type="file" name="ijazah"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                @if ($student->biodata && $student->biodata->hasMedia('daftarUlang'))
                                    @foreach ($student->biodata->getMedia('daftarUlang') as $media)
                                        @if ($media->name == 'ijazah')
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="text-blue-600 hover:underline">Lihat Ijazah</a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <!-- Pernyataan Diri -->
                            <div class="flex flex-col">
                                <label for="pernyataan_diri" class="font-medium text-gray-700">Scan Surat Pernyataan
                                    Diri</label>
                                <a href="{{ asset('assets/berkas-daftar-ulang/Surat Pernyataan Mahasiswa Baru.pdf') }}"
                                    class="text-blue-600 text-sm hover:text-blue-800" target="_BLANK">Unduh template</a>
                                <input type="file" name="pernyataan_diri"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                @if ($student->biodata && $student->biodata->hasMedia('daftarUlang'))
                                    @foreach ($student->biodata->getMedia('daftarUlang') as $media)
                                        @if ($media->name == 'pernyataan_diri')
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="text-blue-600 hover:underline">Lihat Surat Pernyataan Diri</a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>

                            <!-- Keterangan Penghasilan -->
                            {{-- <div class="flex flex-col">
                                <label for="keterangan_penghasilan" class="font-medium text-gray-700">Scan Surat
                                    Keterangan Penghasilan Orang Tua</label>
                                <a href="{{ asset('assets/berkas-daftar-ulang/Surat Penghasilan Orang Tua Peserta Didik.pdf') }}"
                                    class="text-blue-600 text-sm hover:text-blue-800" target="_BLANK">Unduh template</a>
                                <input type="file" name="keterangan_penghasilan"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                @if ($student->biodata && $student->biodata->hasMedia('daftarUlang'))
                                    @foreach ($student->biodata->getMedia('daftarUlang') as $media)
                                        @if ($media->name == 'keterangan_penghasilan')
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="text-blue-600 hover:underline">Lihat Keterangan Penghasilan</a>
                                        @endif
                                    @endforeach
                                @endif
                            </div> --}}

                            <!-- Pernyataan Orang Tua -->
                            <div class="flex flex-col">
                                <label for="pernyataan_ortu" class="font-medium text-gray-700">Scan Surat Pernyataan
                                    Orang Tua</label>
                                <a href="{{ asset('assets/berkas-daftar-ulang/Surat Pernyataan Orang Tua.pdf') }}"
                                    class="text-blue-600 text-sm hover:text-blue-800" target="_BLANK">Unduh template</a>
                                <input type="file" name="pernyataan_ortu"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                @if ($student->biodata && $student->biodata->hasMedia('daftarUlang'))
                                    @foreach ($student->biodata->getMedia('daftarUlang') as $media)
                                        @if ($media->name == 'pernyataan_ortu')
                                            <a href="{{ $media->getUrl() }}" target="_blank"
                                                class="text-blue-600 hover:underline">Lihat Surat Pernyataan Orang
                                                Tua</a>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">Selanjutnya</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('layouts.credit')

</x-app-layout>
