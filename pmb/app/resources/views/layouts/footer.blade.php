@php
    $siteLogo = $siteLogo ?? \App\Models\Setting::get('site_logo');
    $waAdmin = $waAdmin ?? \App\Models\Setting::get('wa_admin', '628813709234');
@endphp
<footer class="bg-slate-900 text-slate-300">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-8 lg:px-12">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Identitas --}}
            <div class="lg:col-span-2">
                <div class="flex items-center gap-3">
                    <img src="{{ $siteLogo ? Storage::url($siteLogo) : asset('/img/logo.webp') }}"
                        alt="Logo BIM University" class="h-11 w-auto rounded-md bg-white/95 p-1">
                    <div>
                        <p class="text-base font-bold text-white">BIM University</p>
                        <p class="text-xs text-slate-400">Universitas Bali Internasional Muhammadiyah</p>
                    </div>
                </div>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-400">
                    Kampus bisnis berstandar internasional yang berkomitmen mencetak lulusan unggul,
                    inovatif, dan berdaya saing global melalui pendidikan yang relevan dengan kebutuhan
                    dunia kerja dan kewirausahaan.
                </p>
            </div>

            {{-- Kontak --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wide text-white">Kontak Admisi</h4>
                <ul class="mt-4 space-y-3 text-sm text-slate-400">
                    <li class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0 text-amber-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                        </svg>
                        <span>Admisi: +62 881-3709-234</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0 text-amber-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                        <a href="mailto:info@bim.ac.id" class="transition hover:text-amber-400">info@bim.ac.id</a>
                    </li>
                    <li class="flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="size-5 shrink-0 text-amber-400">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        <span>Jl. Bypass Ngurah Rai No.19, Sidakarya, Denpasar Selatan, Kota Denpasar, Bali 80224</span>
                    </li>
                </ul>
            </div>

            {{-- Tautan --}}
            <div>
                <h4 class="text-sm font-semibold uppercase tracking-wide text-white">Tautan</h4>
                <ul class="mt-4 space-y-3 text-sm text-slate-400">
                    <li><a href="https://bim.ac.id" target="_blank" rel="noopener"
                            class="transition hover:text-amber-400">Website Resmi bim.ac.id</a></li>
                    <li><a href="#program-studi" class="transition hover:text-amber-400">Program Studi</a></li>
                    <li><a href="#formulir" class="transition hover:text-amber-400">Formulir Pendaftaran</a></li>
                    <li><a href="{{ route('login') }}" class="transition hover:text-amber-400">Masuk Akun Pendaftar</a></li>
                    <li>
                        <a href="https://api.whatsapp.com/send?phone={{ $waAdmin }}" target="_blank" rel="noopener"
                            class="transition hover:text-amber-400">Hubungi Admin via WhatsApp</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>
