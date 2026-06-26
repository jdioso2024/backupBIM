<x-app-layout>
    <div class="py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-3">
                <a href="{{ route('admrektorat.kip.dashboard') }}"
                    class="inline-flex justify-center mt-2 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full sm:w-auto">Lihat Data
                    Pendaftar KIP Kuliah</a>
            </div>
            <div class="grid grid-cols-1">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-6">
                        <form method="GET" action="{{ route('admrektorat.dashboard') }}" class="mb-4 w-full sm:w-auto">
                            <div class="flex flex-col sm:flex-row sm:space-x-4">
                                <div class="mb-4 sm:mb-0">
                                    <label for="per_page"
                                        class="block text-sm font-medium text-gray-700">Tampilkan</label>
                                    <select name="per_page" id="per_page" onchange="this.form.submit()"
                                        class="mt-1 block w-full border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10
                                        </option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25
                                        </option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                        </option>
                                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-4 sm:mb-0">
                                    <label for="filter_program" class="block text-sm font-medium text-gray-700">Program
                                        Pilihan</label>
                                    <select id="filter_program" name="filter_program"
                                        class="mt-1 block w-full border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option selected disabled>Filter Pilihan</option>
                                        @foreach ($programs as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-4 sm:mb-0">
                                    <label for="filter_choice"
                                        class="block text-sm font-medium text-gray-700">Pilihan</label>
                                    <select id="filter_choice" name="pilihan_prodi"
                                        class="mt-1 block w-full border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option selected disabled>Filter Pilihan</option>
                                        <option value="pertama">Pilihan 1</option>
                                        <option value="kedua">Pilihan 2</option>
                                    </select>
                                </div>
                                <div class="mb-4 sm:mb-0">
                                    <label for="prodi" class="block text-sm font-medium text-gray-700">Program
                                        Studi</label>
                                    <select id="prodi" name="prodi"
                                        class="mt-1 block w-full border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <option selected disabled>Filter Prodi</option>
                                        @foreach ($prodis as $prodi)
                                            <option value="{{ $prodi->code }}">{{ $prodi->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex items-end">
                                    <button type="submit"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Filter
                                    </button>
                                </div>

                            </div>
                        </form>
                        <form action="{{ route('admrektorat.dashboard') }}" method="get">
                            <div class="flex mt-2">
                                <button type="submit"
                                    class="inline-flex justify-center py-2 px-4 border border-black shadow-sm text-sm font-medium rounded-md text-black bg-transparent hover:border-indigo-600 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Reset Filter
                                </button>
                            </div>
                        </form>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <form action="{{ route('admrektorat.export.student') }}" method="get"
                                class="w-full sm:w-auto">
                                @csrf
                                <button type="submit"
                                    class="inline-flex justify-center mt-2 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 w-full sm:w-auto">
                                    Export Data
                                </button>
                            </form>
                            <form action="{{ route('admrektorat.reblast-email') }}" method="post"
                                class="w-full sm:w-auto">
                                @csrf
                                <button type="submit" data-tooltip-target="tooltip-default"
                                    class="inline-flex justify-center mt-2 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 w-full sm:w-auto">
                                    Reblast Email
                                </button>
                                <div id="tooltip-default" role="tooltip"
                                    class="absolute z-10 invisible inline-block px-3 py-2 text-sm font-medium text-white transition-opacity duration-300 bg-gray-900 rounded-lg shadow-sm opacity-0 tooltip dark:bg-gray-700">
                                    Kirim ulang akun mahasiswa yang belum melakukan upload berkas.
                                    <div class="tooltip-arrow" data-popper-arrow></div>
                                </div>
                            </form>

                            <a href="{{ route('admrektorat.promo-code.index') }}"
                                class="inline-flex justify-center mt-2 py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 w-full sm:w-auto">Kode
                                Promo</a>
                        </div>
                    </div>
                    <form method="GET" action="{{ route('admrektorat.dashboard') }}" class="mb-4 w-full sm:w-auto">
                        <div class="mb-4 sm:mb-0">
                            <label for="student" class="block text-sm font-medium text-gray-700">Cari Mahasiswa</label>
                            <input type="text" name="student" id="student"
                                placeholder="Cari Mahasiswa berdasarkan nama" value="{{ request('student') }}"
                                class="mt-1 block w-1/4 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                    </form>
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
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        #
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Tanggal
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Nama Lengkap
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Program
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        No HP/WA
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Program Studi
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Referensi
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Kode Promo
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Bukti Transfer
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Konfirmasi
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    <tr class="bg-white border-b">
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $loop->iteration }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ $student->register_at }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="font-bold">{{ $student->name }}</p>
                                            <p>{{ $student->user->email }}</p>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($student->program->id == 1)
                                                <p class="bg-blue-600 rounded-lg text-white px-2 text-center">REG</p>
                                            @elseif ($student->program->id == 2)
                                                <p class="bg-green-600 rounded-lg text-white px-2 text-center">INT</p>
                                            @else
                                                <p class="bg-teal-600 rounded-lg text-white px-2 text-center">EXE</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $student->phone_number }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-col space-y-2">
                                                <span>{{ $student->prodi1->name }}</span>
                                                <span>{{ $student->prodi2->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @switch($student->referensi)
                                                @case('RS')
                                                    Rekomendasi Sekolah
                                                @break

                                                @case('KsBIM')
                                                    Kegiatan sosialisasi BIM
                                                @break

                                                @case('TM')
                                                    Tokoh masyarakat
                                                @break

                                                @case('SM')
                                                    Social Media
                                                @break

                                                @case('WS')
                                                    Website
                                                @break

                                                @case('FL')
                                                    Flyer / Brosur
                                                @break

                                                @case('SP')
                                                    Spanduk
                                                @break

                                                @case('KR')
                                                    Koran
                                                @break

                                                @case('RD')
                                                    Radio
                                                @break

                                                @case('TK')
                                                    Teman / Keluarga
                                                @break

                                                @default
                                                    Tidak ada referensi
                                            @endswitch
                                        </td>
                                        <td>{{ $student->user->promoCodeUsages->promoCode->code ?? '-' }}</td>
                                        <td class="px-6 py-4">
                                            @if ($student->studentDocument && $student->studentDocument->slip_transfer != null)
                                                <button data-modal-target="default-modal"
                                                    data-modal-toggle="default-modal"
                                                    data-image-url="{{ asset('storage/' . $student->studentDocument->slip_transfer) }}"
                                                    class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2.5 py-1 text-center"
                                                    type="button">
                                                    Lihat File
                                                </button>
                                            @else
                                                Belum
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($student->status == 0 && $student->studentDocument)
                                                @if ($student->studentDocument->slip_transfer != null)
                                                    <form action="{{ route('admrektorat.student.update', $student) }}"
                                                        method="post" id="confirm-form-{{ $student->id }}">
                                                        @csrf
                                                        @method('put')
                                                        <button type="submit"
                                                            onclick="confirmBuktiTransfer({{ $student->id }})"
                                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2.5 rounded-lg">Konfirmasi</button>
                                                    </form>
                                                @else
                                                    <span
                                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        Belum
                                                    </span>
                                                @endif
                                            @elseif ($student->status != 0)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Confirmed
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Uncomfirmed
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($student->status == 1 || $student->status == 5)
                                                <button data-modal-target="status-modal"
                                                    data-modal-toggle="status-modal"
                                                    data-modal-id="{{ $student->id }}"
                                                    data-modal-status="{{ $student->status }}"
                                                    data-modal-isBeasiswa="{{ $student->user->hasPermissionTo('beasiswa-access') }}"
                                                    class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-2.5 py-1 text-center"
                                                    type="button">
                                                    Ubah Status
                                                </button>
                                            @elseif ($student->status == 2)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Diterima Pilihan #1
                                                </span>
                                            @elseif ($student->status == 3)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Diterima Pilihan #2
                                                </span>
                                            @elseif ($student->status == 4)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Ditolak
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div x-data="{ open: false }" class="relative">
                                                <!-- Tombol Ikon -->
                                                <button @click="open = !open"
                                                    class="text-gray-500 hover:text-gray-700 focus:outline-none">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 6v.01M12 12v.01M12 18v.01" />
                                                    </svg>
                                                </button>

                                                <!-- Dropdown Menu -->
                                                <div x-show="open" @click.outside="open = false"
                                                    class="absolute right-0 z-10 w-40 mt-2 origin-top-right bg-white divide-y divide-gray-100 rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                                                    <div class="py-1">
                                                        <form
                                                            action="{{ route('admrektorat.student.destroy', $student) }}"
                                                            id="delete-form-{{ $student->id }}" method="post"
                                                            class="block hover:bg-gray-100">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="button"
                                                                onclick="confirmDelete({{ $student->id }})"
                                                                class="font-medium text-red-600 dark:text-red-500 hover:underline w-full px-4 py-2">Hapus</button>
                                                        </form>
                                                        <a href="{{ route('admrektorat.student.show', $student) }}"
                                                            class="font-medium text-blue-600 dark:text-blue-500 text-center hover:underline block hover:bg-gray-100 w-full px-4 py-2">Detail</a>
                                                        <form
                                                            action="{{ route('admrektorat.reblast-email-student', $student) }}"
                                                            method="post" class="block hover:bg-gray-100">
                                                            @csrf
                                                            <button type="submit"
                                                                class="font-medium text-yellow-600 dark:text-yellow-500 hover:underline w-full px-4 py-2">Reblast</button>
                                                        </form>
                                                        <form
                                                            action="{{ route('admrektorat.reblast-wa-student', $student) }}"
                                                            method="post" class="block hover:bg-gray-100">
                                                            @csrf
                                                            <button
                                                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline w-full px-4 py-2">Blast
                                                                WA</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                                                Tidak ada data siswa.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="p-4">
                                {{ $students->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @push('modals')
            <!-- Main modal -->
            <div id="default-modal" tabindex="-1" aria-hidden="true"
                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative p-4 w-full max-w-2xl max-h-full">
                    <!-- Modal content -->
                    <div class="relative bg-white rounded-lg shadow ">
                        <!-- Modal header -->
                        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                            <h3 class="text-xl font-semibold text-gray-900 ">
                                Bukti Transfer
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center "
                                data-modal-hide="default-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <!-- Modal body -->
                        <div class="p-4 md:p-5 space-y-4">
                            <img id="slipTransferImage" src="" alt="Slip Transfer" class="w-full h-auto">
                            <p class="text-gray-500 text-sm pt-5">*File tidak terlihat?</p>
                            <a id="slipTransferImage2" href="" class="text-blue-500 hover:text-blue-700"
                                target="_blank">Buka file</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Status Modal --}}
            <div id="status-modal" tabindex="-1" aria-hidden="true"
                class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                <div class="relative p-4 w-full max-w-md max-h-full">
                    <!-- Modal content -->
                    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                        <!-- Modal header -->
                        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-">
                            <h3 class="text-lg font-semibold text-gray-900 ">
                                Status Calon Mahasiswa
                            </h3>
                            <button type="button"
                                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                                data-modal-toggle="status-modal">
                                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 14 14">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                        stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                </svg>
                                <span class="sr-only">Close modal</span>
                            </button>
                        </div>
                        <!-- Modal body -->
                        <form id="status-change-form" class="p-4 md:p-5" action="" method="POST">
                            @csrf
                            <div class="grid gap-4 mb-10 grid-cols-2">
                                <div class="col-span-2">
                                    <input type="hidden" id="student-id" name="student_id" value="">
                                    <label for="category" class="block mb-2 text-sm font-medium text-gray-900 ">Status</label>
                                    <select id="category" name="status"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                        <option selected="">Pilih Status Calon Mahasiswa</option>
                                        <option value="berkas">Lulus Seleksi Berkas</option>
                                        <option value="diterima1">Terima Pilihan 1</option>
                                        <option value="diterima2">Terima Pilihan 2</option>
                                        <option value="ditolak">Tolak</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit"
                                class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center ">
                                <svg class="me-1 -ms-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                Submit
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endpush
        @push('scripts')
            <script>
                function confirmDelete(studentId) {
                    if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
                        document.getElementById('delete-form-' + studentId).submit();
                    }
                }

                function confirmBuktiTransfer(studentId) {
                    if (confirm('Apakah Anda yakin ingin mengkonfirmasi bukti transfer ini?')) {
                        document.getElementById('confirm-form-' + studentId).submit();
                    }
                }

                document.addEventListener("DOMContentLoaded", function() {
                    const modalButtons = document.querySelectorAll("[data-modal-toggle]");

                    modalButtons.forEach(button => {
                        button.addEventListener("click", function() {
                            const modalId = this.dataset.modalTarget;
                            const imageUrl = this.dataset.imageUrl;

                            // Set the image source in the modal
                            const modal = document.getElementById(modalId);
                            const image = modal.querySelector("#slipTransferImage");
                            const image2 = modal.querySelector("#slipTransferImage2");
                            image.src = imageUrl;
                            image2.href = imageUrl;
                        });
                    });
                });

                document.addEventListener('DOMContentLoaded', (event) => {
                    const buttons = document.querySelectorAll('button[data-modal-target="status-modal"]');
                    const modal = document.getElementById('status-modal');
                    const form = document.getElementById('status-change-form');
                    const studentIdInput = document.getElementById('student-id');
                    const categorySelect = document.getElementById('category');

                    buttons.forEach(button => {
                        button.addEventListener('click', function() {
                            const studentId = this.getAttribute('data-modal-id');
                            const studentStatus = this.getAttribute('data-modal-status');
                            const isBeasiswa = this.getAttribute('data-modal-isBeasiswa');
                            studentIdInput.value = studentId;
                            form.action = `{{ url('admrektorat/change-status') }}/${studentId}`;

                            if (isBeasiswa == 1) {
                                // Reset the options to be visible
                                [...categorySelect.options].forEach(option => {
                                    option.style.display = 'block';
                                });

                                // Hide options based on status
                                if (studentStatus == 1) {
                                    categorySelect.querySelector('option[value="diterima1"]').style
                                        .display =
                                        'none';
                                    categorySelect.querySelector('option[value="diterima2"]').style
                                        .display =
                                        'none';
                                } else if (studentStatus == 5) {
                                    categorySelect.querySelector('option[value="berkas"]').style.display =
                                        'none';
                                }
                            } else {
                                categorySelect.querySelector('option[value="berkas"]').style.display =
                                    'none';
                            }
                        });
                    });

                    // Close modal on close button click
                    const closeButton = modal.querySelector('button[data-modal-toggle="status-modal"]');
                    closeButton.addEventListener('click', function() {
                        modal.classList.add('hidden');
                    });
                });
            </script>
        @endpush
    </x-app-layout>
