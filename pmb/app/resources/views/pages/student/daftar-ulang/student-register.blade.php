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
                        <li class="flex items-center text-blue-600 dark:text-blue-500 space-x-2.5 rtl:space-x-reverse">
                            <span
                                class="flex items-center justify-center w-8 h-8 border border-blue-600 rounded-full shrink-0 dark:border-blue-500">1</span>
                            <span>
                                <h3 class="font-medium leading-tight">Biodata</h3>
                                <p class="text-sm">Data diri lengkap</p>
                            </span>
                        </li>
                        {{-- @can('beasiswa-access')
                            <li class="flex items-center text-gray-500 dark:text-gray-400 space-x-2.5 rtl:space-x-reverse">
                                <span
                                    class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400">2</span>
                                <span>
                                    <h3 class="font-medium leading-tight">Cetak Kartu Peserta</h3>
                                    <p class="text-sm">Untuk ujian/tes</p>
                                </span>
                            </li>
                        @endcan --}}
                        @canany(['prestasi-access', 'reguler-access', 'kip', 'beasiswa-access'])
                            <li class="flex items-center text-gray-500 dark:text-gray-400 space-x-2.5 rtl:space-x-reverse">
                                <span
                                    class="flex items-center justify-center w-8 h-8 border border-gray-500 rounded-full shrink-0 dark:border-gray-400">2</span>
                                <span>
                                    <h3 class="font-medium leading-tight">Administrasi</h3>
                                    <p class="text-sm">Berkas dan Pernyataan</p>
                                </span>
                            </li>
                        @endcanany
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
                <form
                    action="{{ route('student.daftar-ulang.store', $student) }}"
                    method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="flex flex-col">
                                <label for="name" class="font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" name="name" readonly
                                    value="{{ old('name', $biodata->name ?? $student->name) }}"
                                    class="bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="no_hp" class="font-medium text-gray-700">No HP/WA</label>
                                <input type="text" name="no_hp" readonly
                                    value="{{ old('no_hp', $biodata->nomor_hp ?? $student->phone_number) }}"
                                    class="bg-gray-100 border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="alamat" class="font-medium text-gray-700">Alamat</label>
                                <input type="text" name="alamat" value="{{ old('alamat', $biodata->alamat ?? '') }}"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="tanggal_lahir" class="font-medium text-gray-700">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir"
                                    value="{{ old('tanggal_lahir', $biodata->tanggal_lahir ?? '') }}"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="nik" class="font-medium text-gray-700">Nomor Induk Kependudukan
                                    (NIK)</label>
                                <input type="number" name="nik" value="{{ old('nik', $biodata->nik ?? '') }}"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="nama_orangtua" class="font-medium text-gray-700">Nama Orang Tua/Wali</label>
                                <input type="text" name="nama_orangtua"
                                    value="{{ old('nama_orangtua', $biodata->nama_orangtua ?? '') }}"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="nomor_hp_orangtua" class="font-medium text-gray-700">Nomor HP Orang
                                    Tua/Wali</label>
                                <input type="number" name="nomor_hp_orangtua"
                                    value="{{ old('nomor_hp_orangtua', $biodata->nomor_hp_orangtua ?? '') }}"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="nik_orangtua" class="font-medium text-gray-700">NIK Orang Tua/Wali</label>
                                <input type="number" name="nik_orangtua"
                                    value="{{ old('nik_orangtua', $biodata->nik_orangtua ?? '') }}"
                                    class="border border-gray-300 rounded-lg px-4 py-2 mt-1" required>
                            </div>
                            <div class="flex flex-col">
                                <label for="hubungan" class="font-medium text-gray-700">Hubungan dengan Anda</label>
                                <select id="hubungan" name="hubungan" required
                                    class="border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block px-4 py-2 mt-1">
                                    <option value="" disabled>Pilih</option>
                                    <option value="ortu kandung"
                                        {{ old('hubungan', $biodata->hubungan ?? '') == 'ayah' ? 'selected' : '' }}>
                                        Orang Tua Kandung</option>
                                    <option value="ortu angkat"
                                        {{ old('hubungan', $biodata->hubungan ?? '') == 'ayah' ? 'selected' : '' }}>
                                        Orang Tua Angkat</option>
                                    <option value="saudara kandung"
                                        {{ old('hubungan', $biodata->hubungan ?? '') == 'saudara' ? 'selected' : '' }}>
                                        Saudara Kandung</option>
                                    <option value="lainnya"
                                        {{ old('hubungan', $biodata->hubungan ?? '') == 'lainnya' ? 'selected' : '' }}>
                                        Lainnya</option>
                                </select>
                            </div>
                            @can('beasiswa-access')
                                <div class="flex flex-col">
                                    <label for="nik_orangtua" class="font-medium text-gray-700">Dokumentasi Tempat
                                        Tinggal</label>
                                    @if (
                                        $student->biodata &&
                                            ($dokTemp = $student->biodata->getMedia('daftarUlang')->firstWhere('name', 'dok_tempat_tinggal')))
                                        <input type="file" name="dok_tempat_tinggal"
                                            class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                        <a href="{{ $dokTemp->getUrl() }}" target="_blank"
                                            class="text-blue-600 hover:underline">Lihat file</a>
                                    @else
                                        <input type="file" name="dok_tempat_tinggal"
                                            class="border border-gray-300 rounded-lg px-4 py-2 mt-1">
                                    @endif
                                </div>
                            @endcan
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
