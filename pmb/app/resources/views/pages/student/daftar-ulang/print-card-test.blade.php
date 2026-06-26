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
                        @can('beasiswa-access')
                            <li class="flex items-center text-blue-600 dark:text-blue-500 space-x-2.5 rtl:space-x-reverse">
                                <span
                                    class="flex items-center justify-center w-8 h-8 border border-blue-600 rounded-full shrink-0 dark:border-blue-500">2</span>
                                <span>
                                    <h3 class="font-medium leading-tight">Cetak Kartu Peserta</h3>
                                    <p class="text-sm">Untuk ujian/tes</p>
                                </span>
                            </li>
                        @endcan
                        @canany(['prestasi-access', 'reguler-access', 'kip'])
                            <li class="flex items-center text-gray-500 dark:text-gray-400 space-x-2.5 rtl:space-x-reverse">
                                <span
                                    class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400">2</span>
                                <span>
                                    <h3 class="font-medium leading-tight">Administrasi</h3>
                                    <p class="text-sm">Berkas dan Pernyataan</p>
                                </span>
                            </li>
                            <li class="flex items-center text-gray-500 dark:text-gray-400 space-x-2.5 rtl:space-x-reverse">
                                <span
                                    class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400">3</span>
                                <span>
                                    <h3 class="font-medium leading-tight">Pembayaran</h3>
                                    <p class="text-sm">Sesuai jurusan dan program yang dipilih</p>
                                </span>
                            </li>
                        @endcanany
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
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="font-semibold text-lg">Cetak Kartu Peserta</h3>
                    </div>
                    <div class="bg-white p-6 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="flex justify-between items-center">
                            <div class="flex flex-col items-center space-x-4 space-y-2">
                                <a href="{{ route('student.daftar-ulang.scholarship.printPdf', $student) }}" class="bg-blue-600 px-3 py-2 rounded-md text-white">Unduh Kartu</a>
                                <p class="text-sm">* Harap segera unduh kartu peserta anda</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('layouts.credit')

</x-app-layout>
