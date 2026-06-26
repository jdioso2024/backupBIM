        @php $siteLogo = \App\Models\Setting::get('site_logo'); @endphp
        <img src="{{ $siteLogo ? Storage::url($siteLogo) : asset('/img/logo.webp') }}" class="h-24">
