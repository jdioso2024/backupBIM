<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Tidak Ditemukan') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <div class="mb-4">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Data Mahasiswa Tidak Ditemukan</h3>
                    <p class="text-gray-600 mb-6">
                        Akun Anda belum terhubung dengan data mahasiswa. <br>
                        Silakan hubungi administrator untuk melengkapi data Anda.
                    </p>
                    <div class="flex justify-center gap-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded">
                                Logout
                            </button>
                        </form>
                        <a href="{{ route('profile.edit') }}"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-block">
                            Lihat Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.footer')
    @include('layouts.credit')
</x-app-layout>
