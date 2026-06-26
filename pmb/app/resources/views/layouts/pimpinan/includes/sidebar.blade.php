@php
    $monitorMenus = [
        ['label' => 'Data Rekap',                'route' => 'pimpinan.monitor.rekap'],
        ['label' => 'Data Rekap Pasca Sarjana',  'route' => 'pimpinan.monitor.rekap-pasca'],
        ['label' => 'Daftar Program Studi',      'route' => 'pimpinan.monitor.program-studi'],
        ['label' => 'Laporan Registrasi',        'route' => 'pimpinan.monitor.laporan-registrasi'],
        ['label' => 'Data Detail PMB',           'route' => 'pimpinan.monitor.data-detail'],
        ['label' => 'Perbandingan Per Tahun',    'route' => 'pimpinan.monitor.perbandingan-tahun'],
        ['label' => 'Sebaran Domisili Pendaftar','route' => 'pimpinan.monitor.sebaran-domisili'],
        ['label' => 'Sebaran Asal Sekolah',      'route' => 'pimpinan.monitor.sebaran-sekolah'],
    ];
@endphp

<!-- Desktop sidebar -->
<aside class="z-20 hidden w-64 overflow-y-auto bg-white dark:bg-gray-800 md:block flex-shrink-0">
    <div class="py-4 text-gray-500 dark:text-gray-400">
        <a class="ml-6 flex items-center gap-2 text-lg font-bold text-gray-800 dark:text-gray-200" href="{{ route('pimpinan.dashboard') }}">
            <img src="{{ asset('img/logo.png') }}" alt="logo" class="w-8 h-8" onerror="this.style.display='none'">
            <span>PMB BIM University</span>
        </a>
        <p class="ml-6 mt-1 text-xs uppercase tracking-wider text-purple-500 font-semibold">Panel Pimpinan</p>

        <ul class="mt-6">
            <!-- Dashboard -->
            <li class="relative px-6 py-3">
                @if(request()->routeIs('pimpinan.dashboard'))
                    <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                @endif
                <a class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200 {{ request()->routeIs('pimpinan.dashboard') ? 'text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-400' }}"
                    href="{{ route('pimpinan.dashboard') }}">
                    <svg class="w-5 h-5" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="ml-4">Dashboard Eksekutif</span>
                </a>
            </li>

            <!-- Monitor PMB label -->
            <li class="px-6 pt-5 pb-1">
                <p class="text-xs uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">Monitor PMB</p>
            </li>

            <!-- 8 item monitor langsung -->
            @foreach ($monitorMenus as $m)
                <li class="relative px-6 py-2">
                    @if(request()->routeIs($m['route']))
                        <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                    @endif
                    <a href="{{ route($m['route']) }}"
                        class="inline-flex items-center w-full text-sm transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200
                        {{ request()->routeIs($m['route']) ? 'text-gray-800 dark:text-gray-100 font-semibold' : 'text-gray-600 dark:text-gray-400' }}">
                        <span class="ml-1">{{ $m['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <!-- Logout -->
        <ul class="mt-4 border-t dark:border-gray-700 pt-2">
            <li class="relative px-6 py-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center w-full text-sm font-semibold transition-colors duration-150 hover:text-gray-800 dark:hover:text-gray-200">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="ml-4">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</aside>

<!-- Mobile sidebar backdrop -->
<div x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-10 flex items-end bg-black bg-opacity-50 sm:items-center sm:justify-center" x-cloak></div>

<aside class="fixed inset-y-0 z-20 flex-shrink-0 w-64 mt-16 overflow-y-auto bg-white dark:bg-gray-800 md:hidden"
    x-show="isSideMenuOpen" x-transition:enter="transition ease-in-out duration-150"
    x-transition:enter-start="opacity-0 transform -translate-x-20" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in-out duration-150" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0 transform -translate-x-20" @click.away="closeSideMenu"
    @keydown.escape="closeSideMenu" x-cloak>
    <div class="py-4 text-gray-500 dark:text-gray-400">
        <a class="ml-6 text-lg font-bold text-gray-800 dark:text-gray-200" href="{{ route('pimpinan.dashboard') }}">
            PMB BIM University
        </a>
        <ul class="mt-6">
            <li class="relative px-6 py-3">
                <a class="inline-flex items-center w-full text-sm font-semibold text-gray-800 dark:text-gray-100"
                    href="{{ route('pimpinan.dashboard') }}">
                    <span class="ml-4">Dashboard Eksekutif</span>
                </a>
            </li>

            <li class="px-6 pt-4 pb-1">
                <p class="text-xs uppercase tracking-wider font-semibold text-gray-400 dark:text-gray-500">Monitor PMB</p>
            </li>

            @foreach ($monitorMenus as $m)
                <li class="relative px-6 py-2">
                    @if(request()->routeIs($m['route']))
                        <span class="absolute inset-y-0 left-0 w-1 bg-purple-600 rounded-tr-lg rounded-br-lg" aria-hidden="true"></span>
                    @endif
                    <a href="{{ route($m['route']) }}"
                        class="text-sm transition-colors {{ request()->routeIs($m['route']) ? 'text-gray-800 dark:text-gray-100 font-semibold' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ $m['label'] }}
                    </a>
                </li>
            @endforeach

            <li class="relative px-6 py-3 mt-2 border-t dark:border-gray-700">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center w-full text-sm font-semibold">
                        <span class="ml-4">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</aside>
