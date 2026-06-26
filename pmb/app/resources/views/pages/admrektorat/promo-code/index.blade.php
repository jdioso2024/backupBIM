<x-app-layout>
    <div class="py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
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

                    <!-- Button to Open Modal -->
                    <button data-modal-target="promo-code-modal" data-modal-toggle="promo-code-modal"
                        class="block text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 mb-4"
                        type="button">
                        Buat Kode Promo
                    </button>

                    <!-- Promo Code Modal -->
                    <div id="promo-code-modal" tabindex="-1" aria-hidden="true"
                        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
                        <div class="relative p-4 w-full max-w-lg max-h-full">
                            <!-- Modal Content -->
                            <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
                                <!-- Modal Header -->
                                <div
                                    class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                                        Buat Kode Promo Baru
                                    </h3>
                                    <button type="button"
                                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                                        data-modal-hide="promo-code-modal">
                                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 14 14">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                        </svg>
                                        <span class="sr-only">Tutup</span>
                                    </button>
                                </div>

                                <!-- Modal Body -->
                                <div class="p-4 md:p-5">
                                    <form class="grid grid-cols-1 md:grid-cols-2 gap-4"
                                        action="{{ route('admrektorat.promo-code.store') }}" method="POST">
                                        @csrf
                                        <div>
                                            <label for="code"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Kode
                                                Promo</label>
                                            <input type="text" name="code" id="code"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="Masukkan kode promo" required>
                                        </div>
                                        <div>
                                            <label for="description"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Deskripsi</label>
                                            <input type="text" name="description" id="description"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="Deskripsi kode promo" required>
                                        </div>
                                        <div>
                                            <label for="type"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tipe</label>
                                            <select id="type" name="type"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                                                <option value="fixed">Fixed</option>
                                                <option value="percentage">Percentage</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label for="value"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nilai</label>
                                            <input type="number" name="value" id="value"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="Masukkan nilai" required>
                                        </div>
                                        <div>
                                            <label for="start_date"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Mulai</label>
                                            <input type="date" name="start_date" id="start_date"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                required>
                                        </div>
                                        <div>
                                            <label for="end_date"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Berakhir</label>
                                            <input type="date" name="end_date" id="end_date"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                required>
                                        </div>
                                        <div>
                                            <label for="max_usage"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Maksimal
                                                Penggunaan</label>
                                            <input type="number" name="max_usage" id="max_usage"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                                placeholder="Masukkan maksimal penggunaan" required>
                                        </div>
                                        <div>
                                            <label for="is_active"
                                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Aktif?</label>
                                            <select id="is_active" name="is_active"
                                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white">
                                                <option value="1">Ya</option>
                                                <option value="0">Tidak</option>
                                            </select>
                                        </div>
                                        <div class="col-span-2">
                                            <button type="submit"
                                                class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Buat
                                                Kode Promo</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        #
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Kode
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Deskripsi
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Type
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Value
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Kode Berlaku Sampai
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Maksimal Penggunaan
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Digunakan sebanyak
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
                                @forelse ($promoCodes as $promoCode)
                                    <tr class="bg-white border-b">
                                        <th scope="row"
                                            class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $loop->iteration }}
                                        </th>
                                        <td class="px-6 py-4">
                                            {{ $promoCode->code }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $promoCode->description }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $promoCode->type }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($promoCode->type === 'fixed')
                                                Rp {{ $promoCode->value }}
                                            @else
                                                {{ $promoCode->value }}%
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $promoCode->start_date }} - {{ $promoCode->end_date }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $promoCode->max_usage }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $promoCode->usage_count }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @if ($promoCode->is_active)
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Aktif
                                                </span>
                                            @else
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Tidak Aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('admrektorat.promo-code.show', $promoCode) }}"
                                                class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Detail</a>
                                            <form
                                                action="{{ route('admrektorat.promo-code.destroy', $promoCode) }}" method="post">
                                                @csrf
                                                @method('delete')
                                                <button type="submit"
                                                    class="font-medium text-red-600 dark:text-red-500 hover:underline">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada data kode promo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="text-right">
                    <a href="{{ route('admrektorat.dashboard') }}"
                        class="inline-block text-blue-600 hover:text-blue-800 font-semibold mt-3 text-sm">Kembali ke Halaman Pendaftaran Mahasiswa</a>
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
    @endpush
</x-app-layout>
