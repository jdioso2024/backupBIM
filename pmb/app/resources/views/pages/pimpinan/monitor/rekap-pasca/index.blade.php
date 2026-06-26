<x-pimpinan-layout>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    @endpush

    <div class="container px-6 mx-auto grid">
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Data Rekap Pasca Sarjana</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Rekapitulasi Program S2 &amp; S3 &mdash; TA {{ $tahunSekarang }}/{{ $tahunSekarang + 1 }}</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold text-indigo-700 bg-indigo-100 rounded-full dark:bg-indigo-700 dark:text-indigo-100">
                Pasca Sarjana
            </span>
        </div>

        <!-- Summary Cards -->
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    ['label' => 'Total Kuota',      'value' => $total['kuota'],      'color' => 'text-gray-500 bg-gray-100'],
                    ['label' => 'Total Pendaftar',  'value' => $total['pendaftar'],  'color' => 'text-indigo-500 bg-indigo-100'],
                    ['label' => 'Total Diterima',   'value' => $total['diterima'],   'color' => 'text-blue-500 bg-blue-100'],
                    ['label' => 'Total Registrasi', 'value' => $total['registrasi'], 'color' => 'text-green-500 bg-green-100'],
                ];
            @endphp
            @foreach ($cards as $c)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                    <div class="p-3 mr-4 rounded-full {{ $c['color'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 7l9-5-9-5-9 5 9 5z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">{{ $c['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-700 dark:text-gray-200">{{ number_format($c['value'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chart -->
        <div class="grid gap-6 mb-8 xl:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Distribusi Pendaftar</h4>
                <div id="chart-pasca-donut"></div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Rekap per Program</h4>
                <div id="chart-pasca-bar"></div>
            </div>
        </div>

        <!-- Table -->
        <div class="p-4 mb-8 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Tabel Detail Program Pasca Sarjana</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-3">Program Studi</th>
                        <th class="px-3 py-3">Jenjang</th>
                        <th class="px-3 py-3 text-right">Kuota</th>
                        <th class="px-3 py-3 text-right">Pendaftar</th>
                        <th class="px-3 py-3 text-right">Diterima</th>
                        <th class="px-3 py-3 text-right">Registrasi</th>
                        <th class="px-3 py-3 text-right">Rasio</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach ($prodis as $p)
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-3 py-3 font-medium text-gray-800 dark:text-gray-300">{{ $p['nama'] }}</td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $p['jenjang'] === 'S2' ? 'bg-indigo-100 text-indigo-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $p['jenjang'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['kuota'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['pendaftar'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['diterima'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-green-600 font-semibold">{{ number_format($p['registrasi'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">
                                @php $rasio = $p['pendaftar'] > 0 ? round($p['diterima'] / $p['pendaftar'] * 100) : 0; @endphp
                                <span class="text-xs text-gray-500">{{ $rasio }}% diterima</span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="font-bold text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700">
                        <td class="px-3 py-3" colspan="2">TOTAL</td>
                        <td class="px-3 py-3 text-right">{{ number_format($total['kuota'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($total['pendaftar'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($total['diterima'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right text-green-600">{{ number_format($total['registrasi'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">-</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        const D = {
            labels: @json(array_column($prodis, 'nama')),
            pendaftar: @json(array_column($prodis, 'pendaftar')),
            diterima: @json(array_column($prodis, 'diterima')),
            registrasi: @json(array_column($prodis, 'registrasi')),
        };
        const RP = v => new Intl.NumberFormat('id-ID').format(v);

        new ApexCharts(document.querySelector('#chart-pasca-donut'), {
            chart: { type: 'donut', height: 300, fontFamily: 'Figtree, sans-serif' },
            series: D.pendaftar,
            labels: D.labels,
            legend: { position: 'bottom', fontSize: '11px' },
            tooltip: { y: { formatter: RP } },
            colors: ['#6366f1','#8b5cf6','#a78bfa','#c4b5fd'],
        }).render();

        new ApexCharts(document.querySelector('#chart-pasca-bar'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 3 } },
            dataLabels: { enabled: false },
            colors: ['#6366f1','#3f83f8','#0e9f6e'],
            series: [
                { name: 'Pendaftar',  data: D.pendaftar },
                { name: 'Diterima',   data: D.diterima },
                { name: 'Registrasi', data: D.registrasi },
            ],
            xaxis: { categories: D.labels, labels: { style: { fontSize: '10px' }, rotate: -15 } },
            legend: { position: 'top' },
            tooltip: { y: { formatter: RP } },
        }).render();
    </script>
    @endpush
</x-pimpinan-layout>
