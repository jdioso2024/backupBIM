<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Penerimaan Mahasiswa Baru {{ date('Y') }}/{{ date('Y') + 1 }} &mdash; BIM University</title>
    <meta name="description"
        content="Pendaftaran online Penerimaan Mahasiswa Baru BIM University (Universitas Bali Internasional Muhammadiyah). Pilih program studi unggulan dan daftar sekarang.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-50 font-sans text-slate-700 antialiased">

    @include('layouts.navbar')
    @include('layouts.header')

    <main class="bg-slate-50">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-8 lg:px-12">

            {{-- Bilah akun --}}
            <div
                class="flex flex-wrap items-center gap-x-4 gap-y-2 rounded-xl border border-blue-200 bg-blue-50 px-5 py-4 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6 shrink-0 text-blue-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
                @if (Auth::check())
                    <p class="font-medium text-blue-900">Selamat datang kembali, {{ Auth::user()->name }}!</p>
                    <a href="{{ route('dashboard') }}"
                        class="font-semibold text-blue-700 underline underline-offset-2 hover:text-blue-900">Buka
                        Dashboard</a>
                    <form action="{{ route('logout') }}" method="POST" class="contents">
                        @csrf
                        <button type="submit"
                            class="font-semibold text-slate-500 underline underline-offset-2 hover:text-slate-700">Keluar</button>
                    </form>
                @else
                    <p class="font-medium text-blue-900">Sudah pernah mendaftar dan memiliki akun?</p>
                    <a href="{{ route('login') }}"
                        class="font-semibold text-blue-700 underline underline-offset-2 hover:text-blue-900">Masuk ke
                        akun Anda</a>
                @endif
            </div>

            <div class="mt-8 grid gap-8 lg:grid-cols-3 lg:items-start">

                {{-- ============ KOLOM KIRI ============ --}}
                <div class="space-y-8 lg:col-span-2">

                    {{-- Notifikasi --}}
                    @if ($errors->any())
                        <div class="rounded-xl border border-red-300 bg-red-50 px-5 py-4" role="alert">
                            <strong class="font-bold text-red-700">Terjadi kesalahan!</strong>
                            <ul class="mt-2 list-disc pl-5 text-sm text-red-700">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="rounded-xl border border-green-300 bg-green-50 px-5 py-4 text-sm text-green-800"
                            role="alert">
                            <strong class="font-bold">Berhasil!</strong>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="rounded-xl border border-red-300 bg-red-50 px-5 py-4 text-sm text-red-700"
                            role="alert">
                            <strong class="font-bold">Gagal!</strong>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- Informasi Penerimaan --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                            <span class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </span>
                            <h2 class="text-lg font-bold text-slate-800">Informasi Penerimaan Mahasiswa Baru</h2>
                        </div>
                        <div
                            class="mt-5 space-y-2 text-sm leading-relaxed text-slate-600 [&_a]:font-medium [&_a]:text-blue-600 [&_a:hover]:underline [&_h1]:text-base [&_h1]:font-bold [&_h1]:text-slate-800 [&_h2]:text-base [&_h2]:font-bold [&_h2]:text-slate-800 [&_h3]:font-semibold [&_h3]:text-slate-800 [&_ol]:list-decimal [&_ol]:space-y-1 [&_ol]:pl-5 [&_strong]:font-semibold [&_strong]:text-slate-800 [&_ul]:list-disc [&_ul]:space-y-1 [&_ul]:pl-5">
                            {!! $formulirDescription !!}</div>
                        @if (count($brosur))
                            <div class="mt-5 border-t border-slate-100 pt-5">
                                <p class="mb-3 text-sm font-semibold text-slate-700">Unduh berkas</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($brosur as $b)
                                        <a href="{{ $b['url'] }}" target="_blank" rel="noopener"
                                            class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-blue-300 hover:text-blue-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            {{ $b['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </section>

                    {{-- Jalur Masuk --}}
                    @if (count($jalurMasuk))
                        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                            <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                                <span
                                    class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                                    </svg>
                                </span>
                                <h2 class="text-lg font-bold text-slate-800">Jalur Masuk</h2>
                            </div>
                            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                                @foreach ($jalurMasuk as $jalur)
                                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                        <h3 class="font-semibold text-slate-800">{{ $jalur['title'] }}</h3>
                                        <p class="mt-1 text-justify text-sm text-slate-600">{{ $jalur['desc'] }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Program Studi --}}
                    <section id="program-studi"
                        class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                            <span
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg>
                            </span>
                            <h2 class="text-lg font-bold text-slate-800">Program Studi</h2>
                        </div>
                        <p class="mt-4 text-sm text-slate-500">
                            Pilih program studi yang sesuai dengan minat dan rencana kariermu di BIM University.
                        </p>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            {{-- Bisnis Digital --}}
                            <article
                                class="group rounded-xl border border-slate-200 p-5 transition hover:border-blue-300 hover:shadow-md">
                                <span
                                    class="flex size-11 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                    </svg>
                                </span>
                                <h3 class="mt-3 font-bold text-slate-800">Bisnis Digital</h3>
                                <p class="mt-1.5 text-justify text-sm text-slate-600">
                                    Berfokus pada strategi bisnis berbasis teknologi, pengembangan aplikasi, dan
                                    pengelolaan platform digital &mdash; mulai dari e-commerce, analisis data, hingga
                                    pemasaran digital.
                                </p>
                                <a href="https://bim.ac.id/bisnis-digital/" target="_blank" rel="noopener"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-800">
                                    Selengkapnya
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </article>

                            {{-- Kewirausahaan --}}
                            <article
                                class="group rounded-xl border border-slate-200 p-5 transition hover:border-amber-300 hover:shadow-md">
                                <span
                                    class="flex size-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                    </svg>
                                </span>
                                <h3 class="mt-3 font-bold text-slate-800">Kewirausahaan</h3>
                                <p class="mt-1.5 text-justify text-sm text-slate-600">
                                    Membangun jiwa inovatif dan kreatif mahasiswa: mengidentifikasi peluang, merancang
                                    model bisnis berkelanjutan, hingga memulai dan mengembangkan usaha sendiri.
                                </p>
                                <a href="https://bim.ac.id/kewirausahaan/" target="_blank" rel="noopener"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-amber-600 hover:text-amber-800">
                                    Selengkapnya
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </article>

                            {{-- Teknologi Pangan --}}
                            <article
                                class="group rounded-xl border border-slate-200 p-5 transition hover:border-green-300 hover:shadow-md">
                                <span
                                    class="flex size-11 items-center justify-center rounded-lg bg-green-50 text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                                    </svg>
                                </span>
                                <h3 class="mt-3 font-bold text-slate-800">Teknologi Pangan</h3>
                                <p class="mt-1.5 text-justify text-sm text-slate-600">
                                    Pendidikan dan penelitian di bidang teknologi pangan: proses pengolahan, pengemasan,
                                    hingga inovasi produk pangan yang aman, berkualitas, dan bernilai gizi tinggi.
                                </p>
                                <a href="https://bim.ac.id/teknologi-pangan/" target="_blank" rel="noopener"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-green-600 hover:text-green-800">
                                    Selengkapnya
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </article>

                            {{-- Manajemen --}}
                            <article
                                class="group rounded-xl border border-slate-200 p-5 transition hover:border-purple-300 hover:shadow-md">
                                <span
                                    class="flex size-11 items-center justify-center rounded-lg bg-purple-50 text-purple-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0 .5 1.5m-.5-1.5h-9.5m0 0-.5 1.5m.75-9 3-3 2.148 2.148A12.061 12.061 0 0 1 16.5 7.605" />
                                    </svg>
                                </span>
                                <h3 class="mt-3 font-bold text-slate-800">Manajemen</h3>
                                <p class="mt-1.5 text-justify text-sm text-slate-600">
                                    Mempelajari pengelolaan sumber daya organisasi &mdash; keuangan, manusia, dan
                                    operasional &mdash; untuk mencapai tujuan bisnis. Peluang karier: manajer,
                                    konsultan, analis bisnis, atau wirausaha.
                                </p>
                                <a href="https://bim.ac.id/manajemen/" target="_blank" rel="noopener"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-purple-600 hover:text-purple-800">
                                    Selengkapnya
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </article>

                            {{-- Hukum --}}
                            <article
                                class="group rounded-xl border border-slate-200 p-5 transition hover:border-red-300 hover:shadow-md sm:col-span-2">
                                <span
                                    class="flex size-11 items-center justify-center rounded-lg bg-red-50 text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0 0 12 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 0 1-2.031.352 5.988 5.988 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971Zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 0 1-2.031.352 5.989 5.989 0 0 1-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971Z" />
                                    </svg>
                                </span>
                                <h3 class="mt-3 font-bold text-slate-800">Hukum</h3>
                                <p class="mt-1.5 text-justify text-sm text-slate-600">
                                    Mempelajari sistem hukum, peraturan perundang-undangan, serta penerapannya dalam
                                    kehidupan bermasyarakat dan bernegara &mdash; mencakup hukum pidana, perdata, tata
                                    negara, dan hukum internasional. Lulusan dapat berkarier sebagai pengacara, jaksa,
                                    hakim, notaris, atau konsultan hukum.
                                </p>
                                <a href="https://bim.ac.id/hukum/" target="_blank" rel="noopener"
                                    class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-red-600 hover:text-red-800">
                                    Selengkapnya
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </article>
                        </div>
                    </section>

                    {{-- Formulir Pendaftaran --}}
                    <section id="formulir"
                        class="scroll-mt-24 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                            <span
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-600 text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800">Formulir Pendaftaran</h2>
                                <p class="text-sm text-slate-500">Lengkapi data berikut untuk memulai pendaftaran.</p>
                            </div>
                        </div>

                        @if (!empty($catatanPendaftaran))
                            <div
                                class="mt-5 flex gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                <span>{{ $catatanPendaftaran }}</span>
                            </div>
                        @endif

                        @if ($tampilkanForm)
                            <form action="{{ route('student.store') }}" method="POST" x-data="promoValidation()"
                                class="mt-6">
                                @csrf
                                <div class="grid gap-6">
                                    <div x-data="formHandler()" class="grid gap-6 sm:grid-cols-2">
                                        {{-- Program Pilihan --}}
                                        <div>
                                            <label for="program"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Program
                                                Pilihan</label>
                                            <select id="program" name="program" x-model="selectedProgram"
                                                class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                <option selected>Pilih Program</option>
                                                @foreach ($programs as $program)
                                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        {{-- Jalur Pendaftaran --}}
                                        <div>
                                            <label for="jalur"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Jalur
                                                Pendaftaran</label>
                                            <select id="jalur" name="jalur" x-model="selectedJalur"
                                                class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                <option selected>Pilih Jalur Pendaftaran</option>
                                                <template x-for="jalur in filteredJalur" :key="jalur.id">
                                                    <option :value="jalur.id" x-text="jalur.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    <div x-data="prodiSelector()" class="grid gap-6 sm:grid-cols-2">
                                        <div>
                                            <label for="first_choice"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Prodi Pilihan
                                                Pertama</label>
                                            <select id="first_choice" name="prodi1_id"
                                                x-model="selectedFirstChoice" @change="filterSecondChoices()"
                                                class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                <option value="" disabled
                                                    x-bind:selected="selectedFirstChoice === null">Pilih</option>
                                                @foreach ($prodis as $prodi)
                                                    <option data-code="{{ $prodi->code }}"
                                                        value="{{ $prodi->code }}">{{ $prodi->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('prodi1_id')
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div>
                                            <label for="second_choice"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Prodi Pilihan
                                                Kedua</label>
                                            <select id="second_choice" name="prodi2_id"
                                                x-model="selectedSecondChoice"
                                                class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                                <option value="" disabled
                                                    x-bind:selected="selectedSecondChoice === null">Pilih</option>
                                                <template x-for="prodi in filteredProdis" :key="prodi.code">
                                                    <option :data-code="prodi.code" :value="prodi.code"
                                                        x-text="prodi.name"></option>
                                                </template>
                                            </select>
                                            @error('prodi2_id')
                                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Nama Lengkap --}}
                                    <div>
                                        <label for="first_name"
                                            class="mb-1.5 block text-sm font-medium text-slate-700">Nama
                                            Lengkap</label>
                                        <input type="text" id="first_name" name="name"
                                            class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                            placeholder="Nama lengkap sesuai ijazah/rapor" required />
                                        @error('name')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Email & Telepon --}}
                                    <div x-data="emailPhoneValidation()" class="grid gap-6 sm:grid-cols-2">
                                        <div>
                                            <label for="email"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                                            <input type="email" id="email" name="email" x-model="email"
                                                @input="validateEmail()"
                                                :class="{ 'border-red-500': emailError, 'border-slate-300': !emailError }"
                                                class="block w-full rounded-lg border bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                                placeholder="nama@email.com" required />
                                            <p x-show="emailError" class="mt-1 text-xs text-red-500"
                                                x-text="emailError"></p>
                                        </div>
                                        <div>
                                            <label for="phone"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Nomor
                                                Telepon / WhatsApp</label>
                                            <input type="tel" id="phone" name="phone_number" x-model="phone"
                                                @input="validatePhone()"
                                                :class="{ 'border-red-500': phoneError, 'border-slate-300': !phoneError }"
                                                class="block w-full rounded-lg border bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                                placeholder="08xxxxxxxxxx" required />
                                            <p x-show="phoneError" class="mt-1 text-xs text-red-500"
                                                x-text="phoneError"></p>
                                        </div>
                                    </div>

                                    {{-- Data Orang Tua --}}
                                    <div x-data="emailPhoneValidation()" class="grid gap-6 sm:grid-cols-2">
                                        <div>
                                            <label for="parent_name"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Nama Orang
                                                Tua / Wali</label>
                                            <input type="text" id="parent_name" name="parent_name"
                                                class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                                placeholder="Nama lengkap orang tua/wali" required />
                                        </div>
                                        <div>
                                            <label for="parent_phone"
                                                class="mb-1.5 block text-sm font-medium text-slate-700">Nomor
                                                Telepon Orang Tua</label>
                                            <input type="number" id="parent_phone" name="parent_phone"
                                                x-model="phone" @input="validatePhone()"
                                                :class="{ 'border-red-500': phoneError, 'border-slate-300': !phoneError }"
                                                class="block w-full rounded-lg border bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                                                placeholder="Nomor HP orang tua/wali" required />
                                            <p x-show="phoneError" class="mt-1 text-xs text-red-500"
                                                x-text="phoneError"></p>
                                        </div>
                                    </div>

                                    {{-- Referensi --}}
                                    <div>
                                        <label for="referensi"
                                            class="mb-1.5 block text-sm font-medium text-slate-700">Dari mana Anda
                                            mengetahui BIM University?</label>
                                        <select id="referensi" name="referensi"
                                            class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                                            <option selected>Pilih</option>
                                            <option value="RS">Rekomendasi Sekolah</option>
                                            <option value="KsBIM">Kegiatan Sosialisasi BIM</option>
                                            <option value="TM">Tokoh Masyarakat</option>
                                            <option value="SM">Media Sosial</option>
                                            <option value="WS">Website</option>
                                            <option value="FL">Flyer / Brosur</option>
                                            <option value="SP">Spanduk</option>
                                            <option value="KR">Koran</option>
                                            <option value="RD">Radio</option>
                                            <option value="TK">Teman / Keluarga</option>
                                        </select>
                                        @error('referensi')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- Kode Promo --}}
                                    <div>
                                        <label for="promo_code"
                                            class="mb-1.5 block text-sm font-medium text-slate-700">Kode Promo
                                            <span class="font-normal text-slate-400">(opsional)</span></label>
                                        <input type="text" id="promo_code" name="promo_code"
                                            class="block w-full rounded-lg border border-slate-300 bg-slate-50 p-2.5 text-sm text-slate-900 transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200 sm:w-3/5"
                                            placeholder="Masukkan kode promo bila ada" x-model="promoCode"
                                            @input="checkPromo()">
                                        <p x-text="message" class="mt-2 text-sm"
                                            :class="{ 'text-green-600': valid, 'text-red-500': !valid }"></p>
                                    </div>
                                </div>

                                <div class="mt-8 border-t border-slate-100 pt-6">
                                    <button type="submit" :disabled="!valid"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
                                        Kirim Pendaftaran
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </button>
                                    <p class="mt-3 text-xs text-slate-400">
                                        Dengan mengirim formulir ini, Anda menyatakan data yang diisi benar dan dapat
                                        dipertanggungjawabkan.
                                    </p>
                                </div>
                            </form>
                        @else
                            <div class="mt-6 rounded-xl border border-blue-200 bg-blue-50 px-6 py-10 text-center">
                                <span
                                    class="mx-auto flex size-11 items-center justify-center rounded-full bg-blue-600 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" class="size-6">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                    </svg>
                                </span>
                                <h3 class="mt-4 text-base font-bold text-slate-800">Pendaftaran Online</h3>
                                <p class="mx-auto mt-1.5 max-w-md text-sm text-slate-600">
                                    Pendaftaran calon mahasiswa baru BIM University dilakukan melalui portal
                                    pendaftaran online resmi.
                                </p>
                                <a href="https://ods.bim.ac.id"
                                    class="mt-5 inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300">
                                    Daftar di ods.bim.ac.id
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        @endif
                    </section>
                </div>

                {{-- ============ KOLOM KANAN ============ --}}
                <aside class="space-y-6 lg:sticky lg:top-24">

                    {{-- Kampus ramah destinasi wisata --}}
                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <img src="{{ asset('img/ilustrasi2.jpeg') }}" alt="Kampus ramah destinasi wisata"
                            class="w-full" loading="lazy">
                        <div class="p-6">
                            <h2 class="text-base font-bold text-slate-800">Kampus ramah destinasi wisata</h2>
                            <p class="mt-1.5 text-sm leading-relaxed text-slate-600">
                                Berlokasi di Denpasar, Bali, BIM University menghadirkan suasana belajar yang nyaman
                                dan dekat dengan beragam destinasi wisata kelas dunia.
                            </p>
                        </div>
                    </section>

                    {{-- Alur Pendaftaran --}}
                    <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                            <span
                                class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </span>
                            <h2 class="text-lg font-bold text-slate-800">Alur Pendaftaran</h2>
                        </div>
                        <ol class="mt-5 space-y-1">
                            @foreach ($alurPendaftaran as $i => $step)
                                <li class="relative flex gap-4 pb-6 last:pb-0">
                                    @unless ($loop->last)
                                        <span class="absolute left-[15px] top-9 h-[calc(100%-1.5rem)] w-px bg-slate-200"></span>
                                    @endunless
                                    <span
                                        class="z-10 flex size-8 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white">
                                        {{ $i + 1 }}
                                    </span>
                                    <div class="pt-1 text-sm text-slate-600">{!! $step['text'] !!}</div>
                                </li>
                            @endforeach
                        </ol>
                    </section>

                    {{-- Bantuan --}}
                    <section
                        class="rounded-2xl border border-blue-200 bg-gradient-to-br from-blue-600 to-blue-700 p-6 text-white shadow-sm">
                        <h2 class="text-base font-bold">Butuh bantuan?</h2>
                        <p class="mt-1 text-sm text-blue-100">
                            Tim Admisi BIM University siap membantu menjawab pertanyaan seputar pendaftaran.
                        </p>
                        <a href="https://api.whatsapp.com/send?phone={{ $waAdmin }}&text=Halo%20Admin%20%F0%9F%98%8A%20Saya%20ingin%20bertanya%20tentang%20Penerimaan%20Mahasiswa%20Baru%20BIM%20University."
                            target="_blank" rel="noopener"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-amber-400 px-4 py-2.5 text-sm font-bold text-slate-900 transition hover:bg-amber-300">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="size-4">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                            </svg>
                            Chat via WhatsApp
                        </a>
                        <p class="mt-3 text-center text-xs text-blue-100">atau email ke
                            <a href="mailto:info@bim.ac.id" class="font-semibold underline">info@bim.ac.id</a>
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    @include('layouts.footer')
    @include('layouts.credit')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const programDropdown = document.getElementById('program');
            const firstChoiceDropdown = document.getElementById('first_choice');
            const secondChoiceDropdown = document.getElementById('second_choice');

            const updateProdiOptions = () => {
                const selectedProgram = programDropdown.value;
                const allowedCodes = ['BD', 'KW'];

                const updateDropdown = (dropdown) => {
                    const options = dropdown.querySelectorAll('option[data-code]');
                    options.forEach(option => {
                        if (selectedProgram == 2) {
                            option.style.display = allowedCodes.includes(option.getAttribute(
                                'data-code')) ? 'block' : 'none';
                        } else {
                            option.style.display = 'block';
                        }
                    });
                };

                updateDropdown(firstChoiceDropdown);
                updateDropdown(secondChoiceDropdown);
            };

            programDropdown.addEventListener('change', updateProdiOptions);
        });
    </script>

    <script>
        function promoValidation() {
            return {
                promoCode: '',
                message: '',
                valid: true,
                checkPromo() {
                    if (this.promoCode.length > 0) {
                        this.message = 'Memeriksa kode promo...';
                        fetch("{{ route('promo.validate') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                                },
                                body: JSON.stringify({
                                    promo_code: this.promoCode
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                this.valid = data.status === 'valid';
                                this.message = data.message;
                            })
                            .catch(error => {
                                this.valid = false;
                                this.message = 'Kode promo tidak valid atau sudah kedaluwarsa.';
                            });
                    } else {
                        this.message = '';
                        this.valid = true;
                    }
                }
            };
        }

        function formHandler() {
            return {
                selectedProgram: null,
                jalurs: @json($jalurs),
                programs: @json($programs),
                get filteredJalur() {
                    const regularProgram = this.programs.find(program => program.name === 'Regular Class');
                    if (this.selectedProgram == (regularProgram ? regularProgram.id : null)) {
                        return this.jalurs.filter(jalur => ['Beasiswa Parsial', 'Beasiswa Parsial', 'Umum/Reguler']
                            .includes(jalur.name)
                        );
                    } else if (this.selectedProgram == 2 || this.selectedProgram == 3) {
                        return this.jalurs.filter(jalur => jalur.name === 'Umum/Reguler');
                    }
                },
                selectedJalur: null,
            };
        }

        function prodiSelector() {
            return {
                // Data yang diterima dari server
                allProdis: @json($prodis),
                selectedFirstChoice: null,
                selectedSecondChoice: null,
                filteredProdis: [],

                // Inisialisasi
                init() {
                    this.filteredProdis = this.allProdis; // Awalnya semua opsi tersedia
                },

                filterSecondChoices() {
                    if (this.selectedFirstChoice) {
                        // Hanya filter prodi yang berbeda dengan pilihan pertama
                        this.filteredProdis = this.allProdis.filter(
                            prodi => prodi.code !== this.selectedFirstChoice
                        );

                        // Pastikan pilihan kedua tetap valid setelah filtering
                        if (!this.filteredProdis.some(prodi => prodi.code === this.selectedSecondChoice)) {
                            this.selectedSecondChoice = null;
                        }
                    } else {
                        this.filteredProdis = this.allProdis;
                    }
                }
            }
        }

        function emailPhoneValidation() {
            return {
                email: '',
                phone: '',
                emailError: '',
                phoneError: '',

                validateEmail() {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.email)) {
                        this.emailError = 'Email tidak valid.';
                    } else {
                        this.emailError = '';
                    }
                },

                validatePhone() {
                    const phoneRegex = /^[0-9]{10,13}$/;
                    if (!phoneRegex.test(this.phone)) {
                        this.phoneError = 'Nomor telepon harus terdiri dari 10-13 digit.';
                    } else {
                        this.phoneError = '';
                    }
                }
            };
        }
    </script>

</body>

</html>
