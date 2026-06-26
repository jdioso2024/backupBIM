<x-pimpinan-layout>
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
    @endpush

    <div class="container px-6 mx-auto grid">
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Sebaran Asal Sekolah</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Distribusi pendaftar berdasarkan asal sekolah</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold text-teal-700 bg-teal-100 rounded-full">
                Total: {{ number_format($total, 0, ',', '.') }} dari {{ count($sekolah) }} sekolah
            </span>
        </div>

        <!-- Top 3 highlight -->
        <div class="grid gap-6 mb-8 md:grid-cols-3">
            @foreach (array_slice($sekolah, 0, 3, true) as $nama => $jml)
                @php $rank = ['🥇','🥈','🥉'][$loop->index] ?? '#'.($loop->index+1); @endphp
                <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 text-center">
                    <p class="text-3xl mb-1">{{ $rank }}</p>
                    <p class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $nama }}</p>
                    <p class="text-2xl font-bold text-purple-600 mt-2">{{ number_format($jml, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400">pendaftar</p>
                </div>
            @endforeach
        </div>

        <!-- Chart horizontal bar -->
        <div class="p-4 mb-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Top 15 Sekolah Asal Pendaftar</h4>
            <div id="chart-sekolah-full"></div>
        </div>

        <!-- Full table -->
        <div class="p-4 mb-8 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
            <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Daftar Lengkap Sekolah</h4>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-3">Rank</th>
                        <th class="px-3 py-3">Nama Sekolah</th>
                        <th class="px-3 py-3 text-right">Pendaftar</th>
                        <th class="px-3 py-3 text-right">% dari Total</th>
                        <th class="px-3 py-3">Proporsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @php $maxS = !empty($sekolah) ? array_values($sekolah)[0] : 1; @endphp
                    @foreach ($sekolah as $nama => $jml)
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-3 py-3 font-bold text-gray-400">{{ $loop->iteration }}</td>
                            <td class="px-3 py-3 font-medium text-gray-800 dark:text-gray-300">{{ $nama }}</td>
                            <td class="px-3 py-3 text-right font-semibold text-purple-600">{{ number_format($jml, 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-gray-500">{{ $total > 0 ? round($jml / $total * 100, 1) : 0 }}%</td>
                            <td class="px-3 py-3 w-32">
                                <div class="w-full h-2 bg-gray-200 rounded dark:bg-gray-700">
                                    <div class="h-2 bg-teal-500 rounded" style="width: {{ $maxS > 0 ? round($jml / $maxS * 100) : 0 }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        const SK = {
            labels: @json(array_keys($sekolah)),
            values: @json(array_values($sekolah)),
        };
        const RP = v => new Intl.NumberFormat('id-ID').format(v);

        new ApexCharts(document.querySelector('#chart-sekolah-full'), {
            chart: { type: 'bar', height: 480, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
            plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '68%', distributed: true } },
            dataLabels: { enabled: true, formatter: RP, style: { fontSize: '11px' } },
            legend: { show: false },
            series: [{ name: 'Pendaftar', data: SK.values }],
            xaxis: { categories: SK.labels, labels: { style: { fontSize: '11px' } } },
            tooltip: { y: { formatter: RP } },
        }).render();
    </script>
    @endpush
</x-pimpinan-layout>
