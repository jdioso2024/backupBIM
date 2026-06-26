<x-superadmin-layout>
    <div class="container px-6 mx-auto grid">
        <h2 class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200">
            Pengaturan Halaman Utama
        </h2>

        @if (session('success'))
            <div class="mb-4 px-4 py-3 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('superadmin.setting.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Logo --}}
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Logo Website</h3>
                <div class="flex items-center gap-6 mb-2">
                    @if ($settings['site_logo'])
                        <img src="{{ Storage::url($settings['site_logo']) }}" alt="Logo" class="h-16 object-contain border rounded p-1">
                    @else
                        <img src="{{ asset('img/logo.webp') }}" alt="Logo Default" class="h-16 object-contain border rounded p-1">
                    @endif
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Format: JPG, PNG, WEBP, SVG. Maks 2MB.</p>
                        <input type="file" name="logo" accept="image/*"
                            class="text-sm text-gray-700 file:mr-3 file:py-2 file:px-4 file:rounded file:border-0 file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200">
                        @error('logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Deskripsi Formulir --}}
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Deskripsi Formulir</h3>
                <textarea name="formulir_description" rows="4"
                    class="w-full border border-gray-300 rounded-lg p-3 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">{{ old('formulir_description', $settings['formulir_description']) }}</textarea>
                @error('formulir_description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Brosur --}}
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs p-6" x-data="brosurEditor()">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Brosur (Link Download)</h3>
                    <button type="button" @click="add()"
                        class="px-3 py-1 text-sm bg-purple-600 text-white rounded hover:bg-purple-700">+ Tambah</button>
                </div>
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex gap-3 mb-3 items-start">
                        <div class="flex-1 grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">Label / Judul</label>
                                <input type="text" :name="'brosur['+index+'][label]'" x-model="item.label"
                                    placeholder="mis. Unduh Brosur PMB BIM"
                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">URL (Google Drive / link)</label>
                                <input type="url" :name="'brosur['+index+'][url]'" x-model="item.url"
                                    placeholder="https://drive.google.com/..."
                                    class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                            </div>
                        </div>
                        <button type="button" @click="remove(index)"
                            class="mt-5 text-red-500 hover:text-red-700" x-show="items.length > 1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            {{-- Jalur Masuk --}}
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs p-6" x-data="jalurEditor()">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Jalur Masuk</h3>
                    <button type="button" @click="add()"
                        class="px-3 py-1 text-sm bg-purple-600 text-white rounded hover:bg-purple-700">+ Tambah Jalur</button>
                </div>
                <template x-for="(item, index) in items" :key="index">
                    <div class="border border-gray-200 rounded-lg p-4 mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-semibold text-purple-600" x-text="'Jalur ' + (index+1)"></span>
                            <button type="button" @click="remove(index)"
                                class="text-red-500 hover:text-red-700 text-xs" x-show="items.length > 1">Hapus</button>
                        </div>
                        <div class="mb-2">
                            <label class="text-xs text-gray-500 mb-1 block">Judul Jalur</label>
                            <input type="text" :name="'jalur_masuk['+index+'][title]'" x-model="item.title"
                                placeholder="mis. Beasiswa Full"
                                class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Deskripsi</label>
                            <textarea :name="'jalur_masuk['+index+'][desc]'" x-model="item.desc" rows="3"
                                class="w-full border border-gray-300 rounded-lg p-2 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"></textarea>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Detail Pendaftaran --}}
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs p-6">
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4">Detail Pendaftaran</h3>
                <div class="mb-4">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="tampilkan_form" value="1"
                            {{ $settings['tampilkan_form'] == '1' ? 'checked' : '' }}
                            style="width:20px;height:20px;accent-color:#7c3aed;cursor:pointer;">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Tampilkan Form Pendaftaran di Halaman Utama
                        </span>
                    </label>
                    <p class="text-xs text-gray-400 mt-1 ml-8">Nonaktifkan untuk menyembunyikan form isian pendaftaran dari halaman utama.</p>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nomor WhatsApp Admin (format internasional, tanpa +)
                    </label>
                    <input type="text" name="wa_admin" value="{{ old('wa_admin', $settings['wa_admin']) }}"
                        placeholder="628813709234"
                        class="w-full border border-gray-300 rounded-lg p-2.5 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    <p class="text-xs text-gray-400 mt-1">Digunakan untuk tombol "Hubungi Admin" di navbar.</p>
                    @error('wa_admin') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Catatan / Pengumuman di Form Pendaftaran (opsional)
                    </label>
                    <textarea name="catatan_pendaftaran" rows="3"
                        placeholder="Contoh: Pendaftaran gelombang 2 dibuka hingga 31 Agustus 2025."
                        class="w-full border border-gray-300 rounded-lg p-3 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">{{ old('catatan_pendaftaran', $settings['catatan_pendaftaran']) }}</textarea>
                    @error('catatan_pendaftaran') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Alur Pendaftaran --}}
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-xs p-6" x-data="alurEditor()">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Alur Pendaftaran</h3>
                    <button type="button" @click="addStep()"
                        class="px-3 py-1 text-sm bg-purple-600 text-white rounded hover:bg-purple-700">+ Tambah Langkah</button>
                </div>
                <p class="text-xs text-gray-400 mb-4">Mendukung HTML. Untuk tombol/link gunakan panel di bawah textarea.</p>
                <template x-for="(step, index) in steps" :key="index">
                    <div class="mb-4 border border-gray-200 rounded-lg p-3">
                        <div class="flex gap-3 items-start mb-2">
                            <span class="mt-2 text-lg font-bold text-purple-600 w-6 flex-shrink-0" x-text="index + 1"></span>
                            <textarea :name="'alur_pendaftaran[' + index + ']'" rows="3" x-model="step.text"
                                :id="'alur_step_' + index"
                                class="flex-1 border border-gray-300 rounded-lg p-2 text-sm text-gray-700 focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600"></textarea>
                            <button type="button" @click="removeStep(index)"
                                class="mt-2 text-red-500 hover:text-red-700" x-show="steps.length > 1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                        {{-- Helper insert link/tombol --}}
                        <div class="ml-9 flex flex-wrap gap-2 items-center" x-data="{ showHelper: false, linkUrl: '', linkLabel: '', btnType: 'link' }">
                            <button type="button" @click="showHelper = !showHelper"
                                class="text-xs px-2 py-1 bg-blue-50 text-blue-600 border border-blue-200 rounded hover:bg-blue-100">
                                🔗 Sisipkan Link / Tombol
                            </button>
                            <div x-show="showHelper" class="w-full mt-2 p-3 bg-gray-50 rounded-lg border border-gray-200 grid gap-2">
                                <div class="flex gap-2">
                                    <div class="flex-1">
                                        <label class="text-xs text-gray-500">Label / Teks</label>
                                        <input type="text" x-model="linkLabel" placeholder="mis. Daftar Sekarang"
                                            class="w-full border border-gray-300 rounded p-1.5 text-sm mt-0.5">
                                    </div>
                                    <div class="flex-1">
                                        <label class="text-xs text-gray-500">URL</label>
                                        <input type="text" x-model="linkUrl" placeholder="https://..."
                                            class="w-full border border-gray-300 rounded p-1.5 text-sm mt-0.5">
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">Tampilan</label>
                                        <select x-model="btnType" class="w-full border border-gray-300 rounded p-1.5 text-sm mt-0.5">
                                            <option value="link">Link biasa</option>
                                            <option value="button">Tombol biru</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="button"
                                    @click="insertLink(index, linkLabel, linkUrl, btnType); showHelper=false; linkUrl=''; linkLabel='';"
                                    class="px-3 py-1.5 text-sm bg-purple-600 text-white rounded hover:bg-purple-700 w-fit">
                                    Sisipkan
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mb-8">
                <button type="submit"
                    class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    Simpan Pengaturan
                </button>
                <a href="{{ url('/') }}" target="_blank"
                    class="ml-4 px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-lg hover:bg-gray-200">
                    Lihat Halaman Utama
                </a>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function brosurEditor() {
            return {
                items: @json(count($settings['brosur']) ? $settings['brosur'] : [['label'=>'','url'=>'']]),
                add()  { this.items.push({ label: '', url: '' }); },
                remove(i) { this.items.splice(i, 1); }
            }
        }
        function jalurEditor() {
            return {
                items: @json(count($settings['jalur_masuk']) ? $settings['jalur_masuk'] : [['title'=>'','desc'=>'']]),
                add()  { this.items.push({ title: '', desc: '' }); },
                remove(i) { this.items.splice(i, 1); }
            }
        }
        function alurEditor() {
            return {
                steps: @json(count($settings['alur_pendaftaran']) ? $settings['alur_pendaftaran'] : [['text'=>'']]),
                addStep()     { this.steps.push({ text: '' }); },
                removeStep(i) { this.steps.splice(i, 1); },
                insertLink(index, label, url, type) {
                    if (!label || !url) return;
                    let html = '';
                    if (type === 'button') {
                        html = `<a href="${url}" target="_blank" class="inline-block mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700">${label}</a>`;
                    } else {
                        html = `<a href="${url}" target="_blank" class="text-blue-600 hover:underline">${label}</a>`;
                    }
                    this.steps[index].text += (this.steps[index].text ? '\n' : '') + html;
                }
            }
        }
    </script>
    @endpush
</x-superadmin-layout>
