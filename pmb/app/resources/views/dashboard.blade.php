<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data dan Kelengkapan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="pb-10">
                @if (session('successDaftarUlang'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                        role="alert">
                        <strong class="font-bold">Berhasil!</strong>
                        <span class="block sm:inline">{{ session('successDaftarUlang') }}</span>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Mahasiswa</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Nama Lengkap</span>
                            <span class="text-gray-900">{{ $student->name }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Email</span>
                            <span class="text-gray-900">{{ $student->user->email }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">No HP/WA</span>
                            <span class="text-gray-900">{{ $student->phone_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Tanggal Registrasi</span>
                            <span class="text-gray-900">{{ $student->register_at }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Prodi Pilihan</span>
                            <span class="text-gray-900">{{ $student->prodi1->name }} /
                                {{ $student->prodi2->name }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Pendaftaran</h3>
                    <div class="flex flex-col gap-y-3">
                        <p>Jalur Pendaftaran: {{ $student->jalurPendaftaran->name }}</p>
                        <span
                            class="inline-block px-4 py-2 rounded-lg text-sm font-medium {{ $student->status == 2 || $student->status == 3 || $student->status == 5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            @php
                                $requiredCount = 0;

                                if (auth()->user()->hasPermissionTo('reguler-access') || auth()->user()->hasPermissionTo('prestasi-access')) {
                                    $requiredCount = 7;
                                } elseif (auth()->user()->hasPermissionTo('beasiswa-access')) {
                                    $requiredCount = 10;
                                } else if(auth()->user()->hasPermissionTo('kip')) {
                                    $requiredCount = 5;
                                }
                            @endphp

                            @if ($mediaCount >= $requiredCount && $student->status == 0)
                                Berkas sedang diverifikasi
                            @elseif ($mediaCount <= 4 && $student->status == 0)
                                Berkas Belum Lengkap
                            @elseif ($student->status == 0)
                                Berkas Belum Lengkap
                            @elseif ($student->status == 1)
                                Slip Transfer Sudah dikonfirmasi & Berkas dalam proses verifikasi
                            @elseif ($student->status == 2)
                                Diterima Pilihan #1
                            @elseif ($student->status == 3)
                                Diterima Pilihan #2
                            @elseif ($student->status == 4)
                                Ditolak
                            @elseif ($student->status == 5)
                                Lolos Seleksi Berkas jalur Beasiswa Parsial
                            @else
                                Belum Mengupload Berkas
                            @endif
                        </span>

                        @if ($student->status == 2 || $student->status == 3)
                            <span
                                class="p-4 bg-green-100 text-green-800 text-base font-medium me-2 dark:bg-green-900 dark:text-green-300 rounded-lg">Selamat!
                                Anda dinyatakan diterima pada prodi
                                {{ $student->status == 2 ? 'Pilihan #1' : 'Pilihan #2' }}</span>

                            @canany(['prestasi-access', 'reguler-access', 'kip', 'beasiswa-access'])
                                <a href="{{ route('student.daftar-ulang', $student) }}"
                                    class="p-2 bg-blue-600 hover:bg-blue-800 text-white font-medium text-center rounded-lg">Daftar
                                    Ulang</a>
                            @endcanany
                        {{-- @elseif ($student->status == 5)
                            <span
                                class="p-4 bg-green-100 text-green-800 text-base font-medium me-2 dark:bg-green-900 dark:text-green-300 rounded-lg">Selamat!
                                Anda dinyatakan lolos seleksi berkas jalur Beasiswa Parsial</span>

                            <a href="{{ route('student.daftar-ulang', $student) }}"
                                class="p-2 bg-blue-600 hover:bg-blue-800 text-white font-medium text-center rounded-lg">Daftar
                                Ulang</a> --}}
                        @endif
                    </div>
                </div>
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
            @canany(['reguler-access', 'beasiswa-access', 'prestasi-access'])
                @include('pages.student.form-dashboard.reguler')
            @endcan
            @can('kip')
                @include('pages.student.form-dashboard.kip')
            @endcan
        </div>
    </div>

    @include('layouts.footer')
    @include('layouts.credit')
</x-app-layout>
