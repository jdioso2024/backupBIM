@php
    $waAdmin = $waAdmin ?? \App\Models\Setting::get('wa_admin', '628813709234');
    $tahunAkademik = date('Y') . '/' . (date('Y') + 1);
@endphp
<header class="relative isolate overflow-hidden bg-slate-900">
    {{-- Latar banner --}}
    <img src="{{ asset('img/pmb-banner.jpg') }}" alt=""
        class="absolute inset-0 -z-20 h-full w-full object-cover">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-blue-950/95 via-blue-900/90 to-blue-800/80"></div>

    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-8 sm:py-24 lg:px-12">
        <div class="max-w-3xl">
            {{-- Eyebrow --}}
            <span
                class="inline-flex items-center gap-2 rounded-full bg-amber-400/15 px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-amber-300 ring-1 ring-inset ring-amber-400/30 sm:text-sm">
                <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                Penerimaan Mahasiswa Baru &middot; Tahun Akademik {{ $tahunAkademik }}
            </span>

            {{-- Judul --}}
            <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-6xl">
                Bergabunglah Bersama
                <span class="text-amber-400">BIM University</span>
            </h1>
            <p class="mt-3 text-lg font-semibold text-blue-100 sm:text-xl">
                Universitas Bali Internasional Muhammadiyah
            </p>

            {{-- Deskripsi --}}
            <p class="mt-6 max-w-2xl text-base leading-relaxed text-blue-100/90 sm:text-lg">
                Kampus bisnis berstandar internasional di jantung Pulau Bali. Wujudkan masa depanmu
                melalui pendidikan inovatif, kurikulum yang relevan dengan dunia kerja, serta
                ekosistem yang mendorong kreativitas dan jiwa kewirausahaan.
            </p>

            {{-- Tombol aksi --}}
            <div class="mt-8 flex flex-wrap gap-3 sm:gap-4">
                <a href="https://ods.bim.ac.id"
                    class="inline-flex items-center gap-2 rounded-lg bg-amber-400 px-6 py-3 text-sm font-bold text-slate-900 shadow-lg shadow-amber-500/20 transition hover:bg-amber-300 focus:outline-none focus:ring-4 focus:ring-amber-300/50">
                    Daftar Sekarang
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="https://api.whatsapp.com/send?phone={{ $waAdmin }}&text=Halo%20Admin%20%F0%9F%98%8A%20Saya%20ingin%20berkonsultasi%20tentang%20Penerimaan%20Mahasiswa%20Baru%20BIM%20University."
                    target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-lg border border-white/30 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">
                    Konsultasi via WhatsApp
                </a>
            </div>

            {{-- Keunggulan --}}
            <ul class="mt-12 grid max-w-2xl gap-4 sm:grid-cols-3">
                @foreach ([
                    'Kurikulum berstandar internasional',
                    'Tersedia jalur beasiswa & reguler',
                    'Kampus strategis di Denpasar, Bali',
                ] as $poin)
                    <li class="flex items-start gap-2 text-sm text-blue-50">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            class="mt-0.5 size-5 shrink-0 text-amber-400">
                            <path fill-rule="evenodd"
                                d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>{{ $poin }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</header>
