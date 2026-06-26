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
                        <li class="flex items-center text-gray-500 dark:text-gray-400 space-x-2.5 rtl:space-x-reverse">
                            <span
                                class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400">2</span>
                            <span>
                                <h3 class="font-medium leading-tight">Administrasi</h3>
                                <p class="text-sm">Berkas dan Pernyataan</p>
                            </span>
                        </li>
                        <li class="flex items-center text-blue-600 dark:text-blue-500 space-x-2.5 rtl:space-x-reverse">
                            <span
                                class="flex items-center justify-center w-8 h-8 border border-blue-600 rounded-full shrink-0 dark:border-blue-500">3</span>
                            <span>
                                <h3 class="font-medium leading-tight">Pembayaran</h3>
                                <p class="text-sm">Sesuai jurusan dan program yang dipilih</p>
                            </span>
                        </li>
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
                <form action="{{ route('student.daftar-ulang.pembayaran.store', $student) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="flex flex-col">
                                <label for="bukti_pembayaran" class="font-medium text-gray-700">Bukti Pembayaran Daftar
                                    Ulang</label>
                                <input type="file" name="bukti_pembayaran"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1">

                                @if ($student->biodata && $student->biodata->hasMedia('daftarUlang'))
                                    @php
                                        $mediaItem = $student->biodata
                                            ->getMedia('daftarUlang')
                                            ->where('name', 'bukti_pembayaran')
                                            ->first();
                                    @endphp

                                    @if ($mediaItem)
                                        <div class="mt-2">
                                            <a href="{{ $mediaItem->getUrl() }}" target="_blank"
                                                class="text-blue-500 hover:text-blue-700">
                                                Lihat Bukti Pembayaran yang Diunggah
                                            </a>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="flex {{ $student->user->hasPermissionTo('beasiswa-access') ? 'justify-end' : 'justify-between' }} items-center">
                            @if (!$student->user->hasPermissionTo('beasiswa-access'))
                                <p> Biaya daftar ulang sesuai jurusan dan program kelas yang dipilih. <a
                                        href="{{ route('daftar-ulang') }}"
                                        class="text-blue-500 hover:text-blue-700">Informasi Biaya</a></p>
                            @endif
                            <div class="">
                                <a href="{{ route('dashboard') }}"
                                    class="bg-transparent hover:bg-gray-200 border border-gray-300  py-2 px-4 rounded mt-4">Kembali
                                    ke Dashboard</a>
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4">Selanjutnya</button>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('layouts.credit')

</x-app-layout>
