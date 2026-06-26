<div class="bg-white p-6 shadow-sm sm:rounded-lg mx-auto">
    <h2 class="font-semibold text-xl text-gray-800 mb-4 text-center">Kelengkapan Berkas</h2>

    <div class="bg-blue-100 p-3 rounded-lg flex gap-2 mb-4 text-blue-600 items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
        </svg>
        <p class="text-sm">Ukuran maksimal 2MB. Jenis file: PDF, JPG, JPEG, PNG</p>
    </div>

    <form action="{{ route('kip.storeDocument') }}" method="post" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <!-- Form Header -->
        <div class="border-b border-gray-200 pb-4">
            <h3 class="text-lg font-medium text-gray-900">Upload Dokumen Persyaratan</h3>
            <p class="mt-1 text-sm text-gray-500">Silakan unggah dokumen-dokumen yang diperlukan</p>
        </div>

        <!-- Slip Transfer -->
        <div class="space-y-2">
            <p class="block font-medium text-gray-700">Slip Transfer Biaya Pendaftaran</p>
            @php
                $slip_transfer = $student->biodata->getMedia('student_document')->firstWhere('name', 'slip_transfer');
            @endphp
            <div class="flex items-center gap-4 justify-between">
                <div class="">
                    <input type="file" id="slip_transfer" name="slip_transfer" class="hidden"
                        onchange="updateLabel(this)">
                    <label for="slip_transfer"
                        class="text-sm
                mr-4 py-2 px-4
                rounded-md border-0 font-semibold
                bg-blue-50 text-blue-700
                hover:bg-blue-100 hover:cursor-pointer">
                        {{ $slip_transfer ? 'Update File' : 'Choose File' }}
                    </label>
                    <span id="label-slip_transfer" class="text-sm text-gray-600">
                        @if ($slip_transfer)
                            {{ $slip_transfer->name }}
                        @else
                            No file chosen
                        @endif
                    </span>
                </div>
                @if ($slip_transfer)
                    <a href="{{ $slip_transfer->getUrl() }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                @endif
            </div>
        </div>

        <!-- Ijazah -->
        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Scan Ijazah SMA/SMK</label>
            @php
                $ijazah = $student->biodata->getMedia('student_document')->firstWhere('name', 'ijazah');
            @endphp
            <div class="flex items-center gap-4 justify-between">
                <div class="">
                    <input type="file" id="ijazah" name="ijazah" class="hidden" onchange="updateLabel(this)">
                    <label for="ijazah"
                        class="text-sm
                mr-4 py-2 px-4
                rounded-md border-0 font-semibold
                bg-blue-50 text-blue-700
                hover:bg-blue-100 hover:cursor-pointer">
                        {{ $ijazah ? 'Update File' : 'Choose File' }}
                    </label>
                    <span id="label-ijazah" class="text-sm text-gray-600">
                        @if ($ijazah)
                            {{ $ijazah->name }}
                        @else
                            No file chosen
                        @endif
                    </span>
                </div>
                @if ($ijazah)
                    <a href="{{ $ijazah->getUrl() }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                @endif
            </div>
        </div>

        <!-- Rapot -->
        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Rapot Semester 1-6</label>
            @php
                $rapot = $student->biodata->getMedia('student_document')->firstWhere('name', 'nilai_rapot');
            @endphp
            <div class="flex items-center gap-4 justify-between">
                <div class="">
                    <input type="file" id="nilai_rapot" name="nilai_rapot" class="hidden"
                        onchange="updateLabel(this)">
                    <label for="nilai_rapot"
                        class="text-sm
                mr-4 py-2 px-4
                rounded-md border-0 font-semibold
                bg-blue-50 text-blue-700
                hover:bg-blue-100 hover:cursor-pointer">
                        {{ $rapot ? 'Update File' : 'Choose File' }}
                    </label>
                    <span id="label-nilai_rapot" class="text-sm text-gray-600">
                        @if ($rapot)
                            {{ $rapot->name }}
                        @else
                            No file chosen
                        @endif
                    </span>
                </div>
                @if ($rapot)
                    <a href="{{ $rapot->getUrl() }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                @endif
            </div>
        </div>

        <!-- Pas Foto -->
        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Scan Pas Foto</label>
            @php
                $pasFoto = $student->biodata->getMedia('student_document')->firstWhere('name', 'pas_foto');
            @endphp
            <div class="flex items-center gap-4 justify-between">
                <div class="">
                    <input type="file" id="pas_foto" name="pas_foto" class="hidden"
                        onchange="updateLabel(this)">
                    <label for="pas_foto"
                        class="text-sm
                mr-4 py-2 px-4
                rounded-md border-0 font-semibold
                bg-blue-50 text-blue-700
                hover:bg-blue-100 hover:cursor-pointer">
                        {{ $pasFoto ? 'Update File' : 'Choose File' }}
                    </label>
                    <span id="label-pas_foto" class="text-sm text-gray-600">
                        @if ($pasFoto)
                            {{ $pasFoto->name }}
                        @else
                            No file chosen
                        @endif
                    </span>
                </div>
                @if ($pasFoto)
                    <a href="{{ $pasFoto->getUrl() }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                @endif
            </div>
        </div>

        <!-- Follow IG -->
        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Bukti Follow Instagram BIM (@bim.campus)</label>
            @php
                $follow_ig = $student->biodata->getMedia('student_document')->firstWhere('name', 'follow_ig');
            @endphp
            <div class="flex items-center gap-4 justify-between">
                <div class="">
                    <input type="file" id="follow_ig" name="follow_ig" class="hidden"
                        onchange="updateLabel(this)">
                    <label for="follow_ig"
                        class="text-sm
                mr-4 py-2 px-4
                rounded-md border-0 font-semibold
                bg-blue-50 text-blue-700
                hover:bg-blue-100 hover:cursor-pointer">
                        {{ $follow_ig ? 'Update File' : 'Choose File' }}
                    </label>
                    <span id="label-follow_ig" class="text-sm text-gray-600">
                        @if ($follow_ig)
                            {{ $follow_ig->name }}
                        @else
                            No file chosen
                        @endif
                    </span>
                </div>
                @if ($follow_ig)
                    <a href="{{ $follow_ig->getUrl() }}" target="_blank"
                        class="inline-flex items-center px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 rounded-md hover:bg-blue-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                @endif
            </div>
        </div>

        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Alamat Domisili</label>
            <input type="text" name="address" placeholder="Contoh: Jl. Raya No. 1, Kota"
                value="{{ $student->biodata->alamat }}"
                class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300">
        </div>

        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Pekerjaan Orang Tua</label>
            <input type="text" name="parent_work" value="{{ $student->biodata->parent_work }}"
                class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300">
        </div>

        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Rata-Rata Penghasilan Orang Tua</label>
            <select name="parent_income"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option readonly>Pilih rata-rata penghasilan orang tua</option>
                <option value="< 1.000.000"
                    {{ $student->biodata->parent_income === '< 1.000.000' ? 'selected' : '' }}>
                    < Rp. 1.000.000</option>
                <option value="1.000.001 - 3.000.000"
                    {{ $student->biodata->parent_income === '1.000.001 - 3.000.000' ? 'selected' : '' }}>Rp. 1.000.001
                    -
                    3.000.000</option>
                <option value="3.000.001 - 5.000.000"
                    {{ $student->biodata->parent_income === '3.000.001 - 5.000.000' ? 'selected' : '' }}>Rp. 3.000.001
                    - 5.000.000</option>
                <option value="5.000.001 - 7.000.000"
                    {{ $student->biodata->parent_income === '5.000.001 - 7.000.000' ? 'selected' : '' }}>Rp. 5.000.001
                    - 7.000.000</option>
                <option value="7.000.001 - 10.000.000"
                    {{ $student->biodata->parent_income === '7.000.001 - 10.000.000' ? 'selected' : '' }}>Rp. 7.000.001
                    - 10.000.000</option>
                <option value=">10.000.000"
                    {{ $student->biodata->parent_income === '>10.000.000' ? 'selected' : '' }}>
                    > Rp. 10.000.000</option>
            </select>
            {{-- <input type="number" name="parent_income" placeholder="Penghasilan per bulan"
                value="{{ $student->biodata->parent_income }}"
                class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300"> --}}
        </div>

        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Referensi/Kontak Darurat</label>
            <input type="number" name="emergency_contact" placeholder="Kontak alternatif"
                value="{{ $student->biodata->emergency_contact }}"
                class="w-full border rounded-lg px-4 py-2 focus:ring focus:ring-blue-300">
        </div>

        <div class="space-y-2">
            <label class="block font-medium text-gray-700">Alasan Mengapa Layak Mendapatkan Beasiswa</label>
            <textarea name="reason_scholarship" class="w-full border rounded-lg px-4 py-2 h-40 focus:ring focus:ring-blue-300">{{ $student->biodata->reason_scholarship }}</textarea>
        </div>

        <div class="mt-4 text-center">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-lg">Simpan</button>
        </div>
    </form>
</div>

@push('scripts')
    <script>
        function updateLabel(input) {
            const label = document.getElementById('label-' + input.id);
            if (input.files.length > 0) {
                label.textContent = input.files[0].name;
            }
        }
    </script>
@endpush
