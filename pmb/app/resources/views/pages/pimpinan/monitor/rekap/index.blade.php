<x-pimpinan-layout>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    @endpush

    <div class="container px-6 mx-auto grid">
        <!-- Header -->
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Data Rekap PMB</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Rekapitulasi pendaftaran per Program Studi &mdash; TA {{ $tahunSekarang }}/{{ $tahunSekarang + 1 }}</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    ['label' => 'Total Kuota',      'value' => $total['kuota'],      'color' => 'text-gray-500 bg-gray-100 dark:bg-gray-700'],
                    ['label' => 'Total Pendaftar',  'value' => $total['pendaftar'],  'color' => 'text-purple-500 bg-purple-100 dark:bg-purple-700'],
                    ['label' => 'Total Diterima',   'value' => $total['diterima'],   'color' => 'text-blue-500 bg-blue-100 dark:bg-blue-700'],
                    ['label' => 'Total Registrasi', 'value' => $total['registrasi'], 'color' => 'text-green-500 bg-green-100 dark:bg-green-700'],
                ];
            @endphp
            @foreach ($cards as $c)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                    <div class="p-3 mr-4 rounded-full {{ $c['color'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">{{ $c['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-700 dark:text-gray-200">{{ number_format($c['value'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chart & Table -->
        <div class="grid gap-6 mb-8 xl:grid-cols-2">
            <!-- Donut Chart -->
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Distribusi Pendaftar per Prodi</h4>
                <div id="chart-donut"></div>
            </div>
            <!-- Bar Chart -->
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Perbandingan Prodi</h4>
                <div id="chart-bar"></div>
            </div>
        </div>

        <!-- Detail Table -->
        <div class="p-4 mb-8 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Tabel Rekap per Program Studi</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-3">Program Studi</th>
                        <th class="px-3 py-3">Jenjang</th>
                        <th class="px-3 py-3 text-right">Kuota</th>
                        <th class="px-3 py-3 text-right">Pendaftar</th>
                        <th class="px-3 py-3 text-right">Diterima</th>
                        <th class="px-3 py-3 text-right">Registrasi</th>
                        <th class="px-3 py-3 text-right">% Reg</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach ($prodis as $p)
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-3 py-3 font-medium text-gray-800 dark:text-gray-300">{{ $p['nama'] }}</td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $p['jenjang'] === 'S1' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $p['jenjang'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['kuota'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['pendaftar'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['diterima'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-green-600 font-semibold">{{ number_format($p['registrasi'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">
                                @php $pct = $p['kuota'] > 0 ? round($p['registrasi'] / $p['kuota'] * 100) : 0; @endphp
                                <span class="px-2 py-1 text-xs rounded-full {{ $pct >= 100 ? 'bg-green-100 text-green-700' : ($pct >= 75 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $pct }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="font-bold text-gray-800 dark:text-gray-200 bg-gray-50 dark:bg-gray-700">
                        <td class="px-3 py-3" colspan="2">TOTAL</td>
                        <td class="px-3 py-3 text-right">{{ number_format($total['kuota'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($total['pendaftar'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ number_format($total['diterima'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right text-green-600">{{ number_format($total['registrasi'], 0, ',', '.') }}</td>
                        <td class="px-3 py-3 text-right">{{ $total['kuota'] > 0 ? round($total['registrasi'] / $total['kuota'] * 100) : 0 }}%</td>
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

        // Donut
        new ApexCharts(document.querySelector('#chart-donut'), {
            chart: { type: 'donut', height: 340, fontFamily: 'Figtree, sans-serif' },
            series: D.pendaftar,
            labels: D.labels,
            legend: { position: 'bottom', fontSize: '11px' },
            tooltip: { y: { formatter: RP } },
            plotOptions: { pie: { donut: { size: '65%' } } },
        }).render();

        // Bar
        new ApexCharts(document.querySelector('#chart-bar'), {
            chart: { type: 'bar', height: 340, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '65%' } },
            dataLabels: { enabled: false },
            colors: ['#7e3af2','#3f83f8','#0e9f6e'],
            series: [
                { name: 'Pendaftar',  data: D.pendaftar },
                { name: 'Diterima',   data: D.diterima },
                { name: 'Registrasi', data: D.registrasi },
            ],
            xaxis: { categories: D.labels, labels: { style: { fontSize: '11px' } } },
            legend: { position: 'top' },
            tooltip: { y: { formatter: RP } },
        }).render();
    </script>
    @endpush
</x-pimpinan-layout>
