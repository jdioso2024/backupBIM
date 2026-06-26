<x-pimpinan-layout>
    <div class="container px-6 mx-auto grid">
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Data Detail PMB</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Daftar lengkap pendaftar PMB &mdash; TA {{ $tahunSekarang }}/{{ $tahunSekarang + 1 }}</p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="flex flex-wrap gap-3 mb-6">
            <div class="flex items-center gap-2 px-3 py-2 bg-white rounded-lg shadow-xs dark:bg-gray-800 text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                Filter:
            </div>
            @php
                $statuses = ['Semua', 'Pending', 'Diterima', 'Registrasi', 'Undur Diri'];
            @endphp
            @foreach ($statuses as $s)
                <button onclick="filterTable('{{ $s }}')" id="btn-{{ Str::slug($s) }}"
                    class="px-3 py-2 text-xs font-medium rounded-lg transition-colors
                    {{ $s === 'Semua' ? 'bg-purple-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }} shadow-xs">
                    {{ $s }}
                </button>
            @endforeach
        </div>

        <!-- Table -->
        <div class="p-4 mb-8 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
            <table class="w-full text-sm" id="detail-table">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-3">No</th>
                        <th class="px-3 py-3">Nama Pendaftar</th>
                        <th class="px-3 py-3">Asal Sekolah</th>
                        <th class="px-3 py-3">Program Studi</th>
                        <th class="px-3 py-3">Jalur</th>
                        <th class="px-3 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700" id="detail-tbody">
                    @foreach ($pendaftar as $p)
                        @php
                            $statusColors = [
                                'Registrasi' => 'bg-green-100 text-green-700',
                                'Diterima'   => 'bg-blue-100 text-blue-700',
                                'Pending'    => 'bg-yellow-100 text-yellow-700',
                                'Undur Diri' => 'bg-red-100 text-red-700',
                            ];
                            $sc = $statusColors[$p['status']] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700" data-status="{{ $p['status'] }}">
                            <td class="px-3 py-3 text-gray-400">{{ $p['no'] }}</td>
                            <td class="px-3 py-3 font-medium text-gray-800 dark:text-gray-300">{{ $p['nama'] }}</td>
                            <td class="px-3 py-3">{{ $p['asal'] }}</td>
                            <td class="px-3 py-3">{{ $p['prodi'] }}</td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $p['jalur'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sc }}">
                                    {{ $p['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="mt-3 text-xs text-gray-400 text-right">*Data sampel 10 pendaftar dari total keseluruhan</p>
        </div>
    </div>

    @push('scripts')
    <script>
        function filterTable(status) {
            document.querySelectorAll('[id^="btn-"]').forEach(btn => {
                btn.classList.remove('bg-purple-600', 'text-white');
                btn.classList.add('bg-white', 'dark:bg-gray-800', 'text-gray-600');
            });
            const slug = status.toLowerCase().replace(/ /g, '-');
            const activeBtn = document.getElementById('btn-' + slug);
            if (activeBtn) {
                activeBtn.classList.add('bg-purple-600', 'text-white');
                activeBtn.classList.remove('bg-white', 'text-gray-600');
            }
            document.querySelectorAll('#detail-tbody tr').forEach(row => {
                if (status === 'Semua' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
    @endpush
</x-pimpinan-layout>
