<x-pimpinan-layout>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    @endpush

    <div class="container px-6 mx-auto grid">
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Laporan Registrasi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Laporan mahasiswa yang telah melakukan registrasi ulang &mdash; TA {{ $tahunSekarang }}/{{ $tahunSekarang + 1 }}</p>
            </div>
        </div>

        <!-- Summary: jalur -->
        <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($jalur as $j)
                @php
                    $pct = $totalReg > 0 ? round($j['jumlah'] / $totalReg * 100) : 0;
                    $colors = ['Prestasi'=>'purple','Reguler'=>'blue','Beasiswa'=>'green','KIP'=>'yellow'];
                    $c = $colors[$j['nama']] ?? 'gray';
                @endphp
                <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                    <div class="p-3 mr-4 rounded-full text-{{ $c }}-500 bg-{{ $c }}-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">{{ $j['nama'] }}</p>
                        <p class="text-2xl font-semibold text-gray-700 dark:text-gray-200">{{ number_format($j['jumlah'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400">{{ $pct }}% dari total</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Charts -->
        <div class="grid gap-6 mb-8 xl:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Trend Registrasi Kumulatif per Bulan</h4>
                <div id="chart-reg-line"></div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Registrasi per Jalur Masuk</h4>
                <div id="chart-reg-jalur"></div>
            </div>
        </div>

        <!-- Info total -->
        <div class="p-4 mb-8 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Mahasiswa Registrasi Tahun {{ $tahunSekarang }}</p>
                    <p class="text-4xl font-bold text-gray-800 dark:text-gray-200 mt-1">{{ number_format($totalReg, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Target Kuota</p>
                    <p class="text-2xl font-semibold text-purple-600">1.500</p>
                    <p class="text-xs mt-1 {{ $totalReg >= 1500 ? 'text-green-500' : 'text-orange-400' }}">
                        {{ $totalReg >= 1500 ? '✓ Target tercapai' : round($totalReg / 1500 * 100) . '% dari target' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const REG = {
            bulan: @json($bulan),
            data: @json($dataRegistrasi),
            jalur: @json(array_column($jalur, 'nama')),
            jumlah: @json(array_column($jalur, 'jumlah')),
        };
        const RP = v => new Intl.NumberFormat('id-ID').format(v);

        new ApexCharts(document.querySelector('#chart-reg-line'), {
            chart: { type: 'line', height: 320, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            stroke: { width: 2, curve: 'smooth', dashArray: [4, 4, 0] },
            markers: { size: 3 },
            colors: ['#9ca3af','#60a5fa','#7e3af2'],
            series: Object.keys(REG.data).map(y => ({ name: 'Registrasi ' + y, data: REG.data[y] })),
            xaxis: { categories: REG.bulan },
            legend: { position: 'top' },
            tooltip: { y: { formatter: v => v == null ? '-' : RP(v) } },
        }).render();

        new ApexCharts(document.querySelector('#chart-reg-jalur'), {
            chart: { type: 'pie', height: 320, fontFamily: 'Figtree, sans-serif' },
            series: REG.jumlah,
            labels: REG.jalur,
            colors: ['#7e3af2','#3f83f8','#0e9f6e','#fbbf24'],
            legend: { position: 'bottom' },
            tooltip: { y: { formatter: RP } },
        }).render();
    </script>
    @endpush
</x-pimpinan-layout>
