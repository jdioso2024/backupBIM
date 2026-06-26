<x-pimpinan-layout>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <style>
            #domisili-map-full { height: 480px; border-radius: 0.5rem; z-index: 0; }
            .map-legend { line-height:18px; color:#555; background:rgba(255,255,255,.9); padding:8px 10px; border-radius:6px; box-shadow:0 1px 4px rgba(0,0,0,.2); font-size:12px; }
            .map-legend i { width:16px; height:16px; float:left; margin-right:8px; opacity:.85; border-radius:3px; }
        </style>
    @endpush

    <div class="container px-6 mx-auto grid">
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Sebaran Domisili Pendaftar</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Peta sebaran asal domisili mahasiswa pendaftar PMB</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                Total: {{ number_format($total, 0, ',', '.') }} pendaftar
            </span>
        </div>

        <!-- Peta -->
        <div class="p-4 mb-6 bg-white rounded-lg shadow-xs dark:bg-gray-800">
            <h4 class="mb-3 font-semibold text-gray-800 dark:text-gray-300">Peta Choropleth Indonesia</h4>
            <div id="domisili-map-full"></div>
        </div>

        <!-- Tabel + Bar provinsi -->
        <div class="grid gap-6 mb-8 xl:grid-cols-2">
            <!-- Bar -->
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Top 10 Provinsi</h4>
                @php $top10 = array_slice($domisili, 0, 10, true); $maxVal = !empty($top10) ? max($top10) : 1; @endphp
                <ul class="space-y-3">
                    @forelse ($top10 as $prov => $jml)
                        <li>
                            <div class="flex justify-between mb-1 text-sm">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ Illuminate\Support\Str::title(strtolower($prov)) }}</span>
                                <span class="text-gray-500">{{ number_format($jml, 0, ',', '.') }}</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 rounded dark:bg-gray-700">
                                <div class="h-2 bg-purple-600 rounded" style="width: {{ round($jml / $maxVal * 100) }}%"></div>
                            </div>
                        </li>
                    @empty
                        <li class="text-sm text-gray-400 text-center py-4">Belum ada data domisili.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Full tabel -->
            <div class="p-4 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-y-auto max-h-[480px]">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-gray-300">Seluruh Provinsi</h4>
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white dark:bg-gray-800">
                        <tr class="text-xs font-semibold text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                            <th class="px-2 py-2">#</th>
                            <th class="px-2 py-2">Provinsi</th>
                            <th class="px-2 py-2 text-right">Pendaftar</th>
                            <th class="px-2 py-2 text-right">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y dark:divide-gray-700">
                        @foreach ($domisili as $i => $jml)
                            <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-2 py-2 text-gray-400">{{ $loop->iteration }}</td>
                                <td class="px-2 py-2">{{ Illuminate\Support\Str::title(strtolower($i)) }}</td>
                                <td class="px-2 py-2 text-right font-medium">{{ number_format($jml, 0, ',', '.') }}</td>
                                <td class="px-2 py-2 text-right text-gray-400">{{ $total > 0 ? round($jml / $total * 100, 1) : 0 }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            const DOM = @json($domisili);
            const values = Object.values(DOM);
            const maxVal = Math.max(...values, 1);
            const RP = v => new Intl.NumberFormat('id-ID').format(v);

            const map = L.map('domisili-map-full', { scrollWheelZoom: false, attributionControl: false })
                .setView([-2.5, 118], 4.3);

            const grades = [0, maxVal * 0.1, maxVal * 0.25, maxVal * 0.5, maxVal * 0.75];
            const colors = ['#fff7bc','#fee391','#fec44f','#fe9929','#d95f0e','#993404'];
            function getColor(v) {
                if (!v) return '#f0f0f0';
                return v > grades[4] ? colors[5] : v > grades[3] ? colors[4] : v > grades[2] ? colors[3]
                     : v > grades[1] ? colors[2] : v > grades[0] ? colors[1] : colors[0];
            }
            function valOf(name) {
                if (!name) return 0;
                return DOM[name.toUpperCase().trim()] || 0;
            }
            function titleCase(s) { return s.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()); }

            fetch('https://cdn.jsdelivr.net/gh/superpikar/indonesia-geojson@master/indonesia-province-simple.json')
                .then(r => r.json()).then(geo => {
                    const layer = L.geoJSON(geo, {
                        style: f => ({
                            fillColor: getColor(valOf(f.properties.Propinsi)),
                            weight: 1, opacity: 1, color: '#fff', fillOpacity: 0.85,
                        }),
                        onEachFeature: (f, lyr) => {
                            const nama = f.properties.Propinsi || '-';
                            const v = valOf(nama);
                            lyr.bindTooltip(`<b>${titleCase(nama)}</b><br>${RP(v)} pendaftar`, { sticky: true });
                            lyr.on({
                                mouseover: e => e.target.setStyle({ weight: 2, color: '#666', fillOpacity: 0.95 }),
                                mouseout:  e => layer.resetStyle(e.target),
                            });
                        },
                    }).addTo(map);

                    const legend = L.control({ position: 'bottomright' });
                    legend.onAdd = function () {
                        const div = L.DomUtil.create('div', 'map-legend');
                        div.innerHTML = '<strong>Jml pendaftar</strong><br>';
                        for (let i = 0; i < grades.length; i++) {
                            const from = Math.round(grades[i]);
                            const to   = grades[i + 1] ? Math.round(grades[i + 1]) : null;
                            div.innerHTML += `<i style="background:${getColor(grades[i] + 1)}"></i> ${from}${to ? '&ndash;' + to : '+'}<br>`;
                        }
                        return div;
                    };
                    legend.addTo(map);
                }).catch(() => {
                    document.getElementById('domisili-map-full').innerHTML =
                        '<p class="p-8 text-center text-gray-400 text-sm">Peta gagal dimuat. Periksa koneksi internet.</p>';
                });
        </script>
    @endpush
</x-pimpinan-layout>
