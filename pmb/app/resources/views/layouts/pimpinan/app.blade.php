<!DOCTYPE html>
<html :class="{ 'theme-dark': dark }" x-data="data()" lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'Laravel') }} &mdash; Dashboard Pimpinan</title>

    <link rel="icon" href="{{ asset('img/logo.png') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body>
    <div class="flex h-screen bg-gray-50 dark:bg-gray-900" :class="{ 'overflow-hidden': isSideMenuOpen }">
        @include('layouts.pimpinan.includes.sidebar')
        <div class="flex flex-col flex-1 w-full">
            @include('layouts.pimpinan.includes.header')
            <main class="h-full overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
    <script src="{{ asset('assets/js/init-alpine.js') }}"></script>
</body>

</html>
