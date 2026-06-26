<x-pimpinan-layout>
    <div class="container px-6 mx-auto grid">
        <div class="flex flex-wrap items-end justify-between gap-2 mt-6 mb-6">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-gray-200">Daftar Program Studi</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Seluruh program studi aktif yang membuka penerimaan mahasiswa baru</p>
            </div>
        </div>

        <!-- S1 / D3 -->
        <div class="p-4 mb-6 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
            <div class="flex items-center gap-3 mb-4">
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-700">S1 / D3</span>
                <h4 class="font-semibold text-gray-800 dark:text-gray-300">Program Sarjana &amp; Diploma</h4>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">Nama Program Studi</th>
                        <th class="px-3 py-3">Jenjang</th>
                        <th class="px-3 py-3 text-right">Kuota</th>
                        <th class="px-3 py-3 text-right">Pendaftar</th>
                        <th class="px-3 py-3 text-right">Diterima</th>
                        <th class="px-3 py-3 text-right">Registrasi</th>
                        <th class="px-3 py-3 text-right">Ketersediaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach ($s1 as $i => $p)
                        @php $sisa = $p['kuota'] - $p['registrasi']; @endphp
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-3 py-3 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-3 py-3 font-medium text-gray-800 dark:text-gray-300">{{ $p['nama'] }}</td>
                            <td class="px-3 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $p['jenjang'] === 'S1' ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700' }}">
                                    {{ $p['jenjang'] }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['kuota'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['pendaftar'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">{{ number_format($p['diterima'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right text-green-600 font-semibold">{{ number_format($p['registrasi'], 0, ',', '.') }}</td>
                            <td class="px-3 py-3 text-right">
                                @if ($sisa > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Sisa {{ $sisa }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Penuh</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- S2 / S3 -->
        <div class="p-4 mb-8 bg-white rounded-lg shadow-xs dark:bg-gray-800 overflow-x-auto">
            <div class="flex items-center gap-3 mb-4">
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-indigo-100 text-indigo-700">S2 / S3</span>
                <h4 class="font-semibold text-gray-800 dark:text-gray-300">Program Pasca Sarjana</h4>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 dark:text-gray-400">
                        <th class="px-3 py-3">#</th>
                        <th class="px-3 py-3">Nama Program Studi</th>
                        <th class="px-3 py-3">Jenjang</th>
                        <th class="px-3 py-3 text-right">Kuota</th>
                        <th class="px-3 py-3 text-right">Pendaftar</th>
                        <th class="px-3 py-3 text-right">Diterima</th>
                        <th class="px-3 py-3 text-right">Registrasi</th>
                        <th class="px-3 py-3 text-right">Ketersediaan</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    @foreach ($s2 as $i => $p)
                        @php $sisa = $p['kuota'] - $p['registrasi']; @endphp
                        <tr class="text-gray-700 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-3 py-3 text-gray-400">{{ $i + 1 }}</td>
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
                                @if ($sisa > 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">Sisa {{ $sisa }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-700">Penuh</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-pimpinan-layout>
