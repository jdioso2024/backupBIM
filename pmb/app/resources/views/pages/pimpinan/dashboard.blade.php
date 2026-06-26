<x-pimpinan-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            [x-cloak] { display: none !important; }
            #domisili-map { height: 420px; border-radius: 0.5rem; z-index: 0; }
            .map-legend { line-height: 18px; color: #555; background: rgba(255,255,255,.9); padding: 8px 10px; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,.2); font-size: 12px; }
            .map-legend i { width: 16px; height: 16px; float: left; margin-right: 8px; opacity: .85; border-radius: 3px; }
        </style>
    @endpush

    <div class="container px-6 mx-auto grid">
        <!-- Title -->
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-2">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Dashboard Pimpinan</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ringkasan Penerimaan Mahasiswa Baru &mdash; Tahun Akademik {{ $tahunSekarang }}/{{ $tahunSekarang + 1 }}</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full dark:bg-purple-700 dark:text-purple-100">
                Data dummy &middot; pratinjau tampilan
            </span>
        </div>

        <!-- 1) Summary cards -->
        <div class="grid gap-6 mt-4 mb-8 md:grid-cols-2 xl:grid-cols-4">
            @php
                $cards = [
                    ['label' => 'Pendaftar',  'value' => $total['pendaftar'],  'classes' => 'text-purple-500 bg-purple-100 dark:text-purple-100 dark:bg-purple-500', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['label' => 'Diterima',   'value' => $total['diterima'],   'classes' => 'text-blue-500 bg-blue-100 dark:text-blue-100 dark:bg-blue-500',       'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['label' => 'Registrasi', 'value' => $total['registrasi'], 'classes' => 'text-green-500 bg-green-100 dark:text-green-100 dark:bg-green-500',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                    ['label' => 'Undur Diri',  'value' => $total['undur_diri'], 'classes' => 'text-red-500 bg-red-100 dark:text-red-100 dark:bg-red-500',           'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                ];
            @endphp
            @foreach ($cards as $c)
                <div class="flex items-center p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                    <div class="p-3 mr-4 rounded-full {{ $c['classes'] }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="mb-1 text-sm font-medium text-gray-600 dark:text-gray-400">{{ $c['label'] }}</p>
                        <p class="text-2xl font-semibold text-gray-700 dark:text-gray-200">{{ number_format($c['value'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 1b) Rekap per fakultas -->
        <div class="grid gap-6 mb-8 xl:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Rekap per Fakultas</h4>
                <div id="chart-fakultas"></div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Tabel Rekap per Fakultas</h4>
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                            <th class="px-2 py-2">Fakultas</th>
                            <th class="px-2 py-2 text-right">Daftar</th>
                            <th class="px-2 py-2 text-right">Terima</th>
                            <th class="px-2 py-2 text-right">Registrasi</th>
                            <th class="px-2 py-2 text-right">Undur</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 dark:text-gray-400">
                        @foreach ($fakultas as $f)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-2 py-2 font-medium text-gray-800 dark:text-gray-300">{{ $f['nama'] }}</td>
                                <td class="px-2 py-2 text-right">{{ number_format($f['pendaftar'], 0, ',', '.') }}</td>
                                <td class="px-2 py-2 text-right">{{ number_format($f['diterima'], 0, ',', '.') }}</td>
                                <td class="px-2 py-2 text-right">{{ number_format($f['registrasi'], 0, ',', '.') }}</td>
                                <td class="px-2 py-2 text-right text-red-500">{{ number_format($f['undur_diri'], 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="font-semibold text-gray-800 dark:text-gray-200">
                            <td class="px-2 py-2">TOTAL</td>
                            <td class="px-2 py-2 text-right">{{ number_format($total['pendaftar'], 0, ',', '.') }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($total['diterima'], 0, ',', '.') }}</td>
                            <td class="px-2 py-2 text-right">{{ number_format($total['registrasi'], 0, ',', '.') }}</td>
                            <td class="px-2 py-2 text-right text-red-500">{{ number_format($total['undur_diri'], 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2) Perbandingan antar tahun -->
        <div class="grid gap-6 mb-8 xl:grid-cols-2">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-1 font-semibold text-gray-800 dark:text-gray-300">Perbandingan 6 Tahun Terakhir</h4>
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">{{ $tahunSekarang - 5 }} &ndash; {{ $tahunSekarang }}</p>
                <div id="chart-tahun"></div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-1 font-semibold text-gray-800 dark:text-gray-300">Kumulatif Pendaftar per Bulan</h4>
                <p class="mb-3 text-xs text-gray-500 dark:text-gray-400">Posisi s/d bulan berjalan dibanding tahun-tahun sebelumnya</p>
                <div id="chart-kumulatif"></div>
            </div>
        </div>

        <!-- 3) Sebaran domisili -->
        <div class="grid gap-6 mb-8 xl:grid-cols-3">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 xl:col-span-2">
                <h4 class="mb-3 font-semibold text-gray-800 dark:text-gray-300">Sebaran Domisili Pendaftar</h4>
                <div id="domisili-map"></div>
                <div id="map-fallback" class="hidden p-4 text-sm text-center text-gray-500">
                    Peta gagal dimuat (butuh koneksi internet untuk data GeoJSON). Lihat ringkasan di samping.
                </div>
            </div>
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Provinsi Terbanyak</h4>
                <ul class="space-y-3">
                    @php $maxDom = max($domisiliTop); @endphp
                    @foreach ($domisiliTop as $prov => $jml)
                        <li>
                            <div class="flex justify-between mb-1 text-sm">
                                <span class="font-medium text-gray-700 capitalize dark:text-gray-300">{{ \Illuminate\Support\Str::title(strtolower($prov)) }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ number_format($jml, 0, ',', '.') }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded dark:bg-gray-700">
                                <div class="h-2 bg-purple-600 rounded" style="width: {{ round($jml / $maxDom * 100) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- 4) Asal sekolah -->
        <div class="grid gap-6 mb-8">
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Sebaran Asal Sekolah (Top 10)</h4>
                <div id="chart-sekolah"></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const PMB = {
                fakultas: @json($fakultas),
                tahun: @json($perbandinganTahun),
                bulan: @json($bulan),
                kumulatif: @json($kumulatifPendaftar),
                domisili: @json($domisili),
                sekolah: @json($asalSekolah),
            };
            const RP = (v) => new Intl.NumberFormat('id-ID').format(v);
            const palette = { pendaftar: '#7e3af2', diterima: '#3f83f8', registrasi: '#0e9f6e', undur: '#f05252' };

            document.addEventListener('DOMContentLoaded', function () {
                // --- Rekap per fakultas (grouped column) ---
                const shortFak = PMB.fakultas.map(f => f.nama.replace('Fakultas ', ''));
                new ApexCharts(document.querySelector('#chart-fakultas'), {
                    chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
                    plotOptions: { bar: { columnWidth: '65%', borderRadius: 3 } },
                    dataLabels: { enabled: false },
                    colors: [palette.pendaftar, palette.diterima, palette.registrasi, palette.undur],
                    series: [
                        { name: 'Pendaftar',  data: PMB.fakultas.map(f => f.pendaftar) },
                        { name: 'Diterima',   data: PMB.fakultas.map(f => f.diterima) },
                        { name: 'Registrasi', data: PMB.fakultas.map(f => f.registrasi) },
                        { name: 'Undur Diri', data: PMB.fakultas.map(f => f.undur_diri) },
                    ],
                    xaxis: { categories: shortFak, labels: { style: { fontSize: '11px' }, rotate: -15, trim: true } },
                    legend: { position: 'top' },
                    tooltip: { y: { formatter: RP } },
                }).render();

                // --- Perbandingan antar tahun (grouped column) ---
                new ApexCharts(document.querySelector('#chart-tahun'), {
                    chart: { type: 'bar', height: 320, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
                    plotOptions: { bar: { columnWidth: '70%', borderRadius: 3 } },
                    dataLabels: { enabled: false },
                    colors: [palette.pendaftar, palette.diterima, palette.registrasi, palette.undur],
                    series: [
                        { name: 'Pendaftar',  data: PMB.tahun.map(t => t.pendaftar) },
                        { name: 'Diterima',   data: PMB.tahun.map(t => t.diterima) },
                        { name: 'Registrasi', data: PMB.tahun.map(t => t.registrasi) },
                        { name: 'Undur Diri', data: PMB.tahun.map(t => t.undur_diri) },
                    ],
                    xaxis: { categories: PMB.tahun.map(t => t.tahun) },
                    legend: { position: 'top' },
                    tooltip: { y: { formatter: RP } },
                }).render();

                // --- Kumulatif per bulan (line) ---
                new ApexCharts(document.querySelector('#chart-kumulatif'), {
                    chart: { type: 'line', height: 320, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
                    stroke: { width: [2, 2, 3], curve: 'smooth', dashArray: [4, 4, 0] },
                    markers: { size: 3 },
                    colors: ['#9ca3af', '#60a5fa', palette.pendaftar],
                    series: Object.keys(PMB.kumulatif).map(y => ({ name: 'Pendaftar ' + y, data: PMB.kumulatif[y] })),
                    xaxis: { categories: PMB.bulan },
                    legend: { position: 'top' },
                    tooltip: { y: { formatter: (v) => v == null ? '-' : RP(v) } },
                }).render();

                // --- Asal sekolah (horizontal bar) ---
                const sekolahKeys = Object.keys(PMB.sekolah);
                new ApexCharts(document.querySelector('#chart-sekolah'), {
                    chart: { type: 'bar', height: 380, toolbar: { show: false }, fontFamily: 'Figtree, sans-serif' },
                    plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '65%', distributed: true } },
                    dataLabels: { enabled: true, formatter: RP, style: { fontSize: '11px' } },
                    legend: { show: false },
                    series: [{ name: 'Pendaftar', data: sekolahKeys.map(k => PMB.sekolah[k]) }],
                    xaxis: { categories: sekolahKeys },
                    tooltip: { y: { formatter: RP } },
                }).render();

                // --- Peta choropleth domisili ---
                initDomisiliMap();
            });

            function initDomisiliMap() {
                if (typeof L === 'undefined') { showMapFallback(); return; }
                const data = PMB.domisili;
                const values = Object.values(data);
                const max = Math.max(...values, 1);

                const map = L.map('domisili-map', { scrollWheelZoom: false, attributionControl: false })
                    .setView([-2.5, 118], 4.3);

                // Skala warna (kuning -> merah tua), 5 kelas
                const grades = [0, max * 0.1, max * 0.25, max * 0.5, max * 0.75];
                const colors = ['#fff7bc', '#fee391', '#fec44f', '#fe9929', '#d95f0e', '#993404'];
                function getColor(v) {
                    if (!v) return '#f0f0f0';
                    return v > grades[4] ? colors[5] : v > grades[3] ? colors[4] : v > grades[2] ? colors[3]
                         : v > grades[1] ? colors[2] : v > grades[0] ? colors[1] : colors[0];
                }
                function valOf(name) {
                    if (!name) return 0;
                    const key = name.toUpperCase().trim();
                    return data[key] || 0;
                }

                const url = 'https://cdn.jsdelivr.net/gh/superpikar/indonesia-geojson@master/indonesia-province-simple.json';
                fetch(url).then(r => r.json()).then(geo => {
                    const layer = L.geoJSON(geo, {
                        style: (f) => ({
                            fillColor: getColor(valOf(f.properties.Propinsi)),
                            weight: 1, opacity: 1, color: '#ffffff', fillOpacity: 0.8,
                        }),
                        onEachFeature: (f, lyr) => {
                            const nama = f.properties.Propinsi || '-';
                            const v = valOf(nama);
                            lyr.bindTooltip(`<b>${titleCase(nama)}</b><br>${RP(v)} pendaftar`, { sticky: true });
                            lyr.on({
                                mouseover: (e) => e.target.setStyle({ weight: 2, color: '#666', fillOpacity: 0.95 }),
                                mouseout: (e) => layer.resetStyle(e.target),
                            });
                        },
                    }).addTo(map);

                    // Legend
                    const legend = L.control({ position: 'bottomright' });
                    legend.onAdd = function () {
                        const div = L.DomUtil.create('div', 'map-legend');
                        div.innerHTML = '<strong>Jml pendaftar</strong><br>';
                        for (let i = 0; i < grades.length; i++) {
                            const from = Math.round(grades[i]);
                            const to = grades[i + 1] ? Math.round(grades[i + 1]) : null;
                            div.innerHTML += `<i style="background:${getColor(grades[i] + 1)}"></i> ${from}${to ? '&ndash;' + to : '+'}<br>`;
                        }
                        return div;
                    };
                    legend.addTo(map);
                }).catch(() => showMapFallback());
            }

            function showMapFallback() {
                document.getElementById('domisili-map').classList.add('hidden');
                document.getElementById('map-fallback').classList.remove('hidden');
            }
            function titleCase(s) {
                return s.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
            }
        </script>
    @endpush
</x-pimpinan-layout>
