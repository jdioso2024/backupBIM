<x-pimpinan-layout>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    @endpush

    <div class="container px-6 mx-auto grid">
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Perbandingan Per Tahun</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Analisis tren PMB dari tahun ke tahun</p>
            </div>
        </div>

        <!-- Highlight perubahan -->
        @php
            $last = end($perbandingan);
            $prev = $perbandingan[count($perbandingan) - 2];
            $delta = $last['pendaftar'] - $prev['pendaftar'];
            $pct   = $prev['pendaftar'] > 0 ? round(abs($delta) / $prev['pendaftar'] * 100, 1) : 0;
        @endphp
        <div class="grid gap-6 mb-8 md:grid-cols-3">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 flex items-center gap-4">
                <div class="{{ $delta >= 0 ? 'text-green-500 bg-green-100' : 'text-red-500 bg-red-100' }} p-3 rounded-full">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $delta >= 0 ? 'M5 10l7-7m0 0l7 7M12 3v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Pertumbuhan Pendaftar</p>
                    <p class="text-2xl font-bold {{ $delta >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $delta >= 0 ? '+' : '' }}{{ number_format($delta, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $pct }}% vs tahun lalu</p>
                </div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <p class="text-sm text-gray-500 mb-1">Tahun Terbaik</p>
                @php $best = collect($perbandingan)->sortByDesc('pendaftar')->first(); @endphp
                <p class="text-2xl font-bold text-purple-600">{{ $best['tahun'] }}</p>
                <p class="text-xs text-gray-400">{{ number_format($best['pendaftar'], 0, ',', '.') }} pendaftar</p>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <p class="text-sm text-gray-500 mb-1">Rata-rata Registrasi</p>
                @php $avgReg = round(array_sum(array_column($perbandingan, 'registrasi')) / count($perbandingan)); @endphp
                <p class="text-2xl font-bold text-blue-600">{{ number_format($avgReg, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400">per tahun (6 tahun terakhir)</p>
            </div>
        </div>

        <!-- Charts -->
        <div class="grid gap-6 mb-8 xl:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-1 font-semibold text-gray-800 dark:text-gray-300">Perbandingan 6 Tahun Terakhir</h4>
                <p class="mb-3 text-xs text-gray-500">Grouped bar: Pendaftar, Diterima, Registrasi, Undur Diri</p>
                <div id="chart-tahun-grouped"></div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-1 font-semibold text-gray-800 dark:text-gray-300">Kumulatif Pendaftar per Bulan</h4>
                <p class="mb-3 text-xs text-gray-500">Posisi s/d bulan berjalan dibanding tahun sebelumnya</p>
                <div id="chart-kumulatif-perbandingan"></div>
            </div>
        </div>

        <!-- Detail tabel -->
        <div class="p-4 mb-8 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Tabel Perbandingan Per Tahun</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-3">Tahun</th>
                        <th class="px-3 py-3 text-right">Pendaftar</th>
                        <th class="px-3 py-3 text-right">Diterima</th>
                        <th class="px-3 py-3 text-right">Registrasi</th>
                        <th class="px-3 py-3 text-right">Undur Diri</th>
                        <th class="px-3 py-3 text-right">Growth</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach ($perbandingan as $i => $t)
                        @php
                            $prevPendaftar = $i > 0 ? $perbandingan[$i - 1]['pendaftar'] : 0;
                            $growth = $prevPendaftar > 0 ? round(($t['pendaftar'] - $prevPendaftar) / $prevPendaftar * 100, 1) : 0;
                        @endphp
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 {{ $loop->last ? 'font-semibold text-gray-800 dark:text-gray-200' : '' }}">
                            <td class="px-3 py-3 font-semibold">{{ $t['tahun'] }}{{ $loop->last ? ' ★' : '' }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($t['pendaftar'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($t['diterima'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-green-600">{{ number_format($t['registrasi'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-red-500">{{ number_format($t['undur_diri'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">
                                @if ($i > 0)
                                    <span class="text-xs {{ $growth >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                        {{ $growth >= 0 ? '+' : '' }}{{ $growth }}%
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        const PT = {
            tahun: @json(array_column($perbandingan, 'tahun')),
            pendaftar: @json(array_column($perbandingan, 'pendaftar')),
            diterima: @json(array_column($perbandingan, 'diterima')),
            registrasi: @json(array_column($perbandingan, 'registrasi')),
            undur: @json(array_column($perbandingan, 'undur_diri')),
            bulan: @json($bulan),
            kumulatif: @json($kumulatif),
        };
        const RP = v => new Intl.NumberFormat('id-ID').format(v);

        new ApexCharts(document.querySelector('#chart-tahun-grouped'), {
            chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            plotOptions: { bar: { columnWidth: '70%', borderRadius: 3 } },
            dataLabels: { enabled: false },
            colors: ['#7e3af2','#3f83f8','#0e9f6e','#f05252'],
            series: [
                { name: 'Pendaftar',  data: PT.pendaftar },
                { name: 'Diterima',   data: PT.diterima },
                { name: 'Registrasi', data: PT.registrasi },
                { name: 'Undur Diri', data: PT.undur },
            ],
            xaxis: { categories: PT.tahun },
            legend: { position: 'top' },
            tooltip: { y: { formatter: RP } },
        }).render();

        new ApexCharts(document.querySelector('#chart-kumulatif-perbandingan'), {
            chart: { type: 'line', height: 320, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            stroke: { width: [2, 2, 3], curve: 'smooth', dashArray: [4, 4, 0] },
            markers: { size: 3 },
            colors: ['#9ca3af','#60a5fa','#7e3af2'],
            series: Object.keys(PT.kumulatif).map(y => ({ name: 'Pendaftar ' + y, data: PT.kumulatif[y] })),
            xaxis: { categories: PT.bulan },
            legend: { position: 'top' },
            tooltip: { y: { formatter: v => v == null ? '-' : RP(v) } },
        }).render();
    </script>
    @endpush
</x-pimpinan-layout>
