<x-app-layout>
    <div class="py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-6">

                <!-- Promo Code Information -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Informasi Kode Promo</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p><strong>Kode:</strong> {{ $promoCode->code }}</p>
                            <p><strong>Deskripsi:</strong> {{ $promoCode->description }}</p>
                            <p><strong>Tipe:</strong> {{ $promoCode->type === 'fixed' ? 'Fixed' : 'Percentage' }}</p>
                        </div>
                        <div>
                            <p><strong>Nilai:</strong>
                                {{ $promoCode->type === 'fixed' ? 'Rp ' . number_format($promoCode->value, 0, ',', '.') : $promoCode->value . '%' }}
                            </p>
                            <p><strong>Periode:</strong> {{ $promoCode->start_date }} - {{ $promoCode->end_date }}</p>
                        </div>
                        <div>
                            <p><strong>Maksimal Penggunaan:</strong> {{ $promoCode->max_usage }}</p>
                            <p><strong>Penggunaan Saat Ini:</strong> {{ $promoCode->usage_count }}</p>
                        </div>
                        <div>
                            <p><strong>Status:</strong>
                                @if ($promoCode->is_active)
                                    <span
                                        class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded">Aktif</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold text-red-700 bg-red-100 rounded">Tidak
                                        Aktif</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Promo Code Usage Section -->
                <div class="bg-white shadow-md rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-4">Penggunaan Kode Promo</h2>
                    @if ($promoCode->usages->isEmpty())
                        <p class="text-gray-500">Kode promo ini belum pernah digunakan.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama Pengguna</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Email Pengguna</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Waktu Penggunaan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($promoCode->usages as $usage)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $loop->iteration }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $usage->user->name ?? 'Tidak diketahui' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $usage->user->email ?? 'Tidak diketahui' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">{{ $usage->used_at }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Back Button -->
                <div class="text-right">
                    <a href="{{ route('admrektorat.promo-code.index') }}"
                        class="inline-block text-blue-600 hover:text-blue-800 font-semibold">Kembali ke Daftar Kode
                        Promo</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
