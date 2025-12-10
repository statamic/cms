@php
    use function Statamic\trans as __;
@endphp

<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width" />
<meta name="robots" content="noindex,nofollow" />
<meta name="color-scheme" content="{{ $user?->preferredColorMode() ?? 'auto' }}">

@if (Statamic::pro() && config('statamic.cp.custom_favicon_url'))
    @include('statamic::partials.favicon', ['favicon_url' => config('statamic.cp.custom_favicon_url')])
@else
    <link rel="icon" type="image/png" href="{{ Statamic::cpViteAsset('img/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" type="image/png" href="{{ Statamic::cpViteAsset('img/favicon-16x16.png') }}" sizes="16x16" />
    <link rel="apple-touch-icon" href="{{ Statamic::cpViteAsset('img/apple-touch-icon.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ Statamic::cpViteAsset('img/favicon.ico') }}">
@endif

<script>
    (function () {
        let mode = {!! ($userMode = $user?->preferredColorMode()) ? "'" . $userMode . "'" : 'null' !!};
        if (!mode) mode = localStorage.getItem('statamic.color_mode') ?? 'auto';
        if (mode === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) mode = 'dark';
        if (mode === 'dark') document.documentElement.classList.add('dark');

        let contrast = {!! $user?->getPreference('strict_accessibility') ? "'increased'" : "'auto'" !!};
        if (contrast === 'auto' && window.matchMedia('(prefers-contrast: more)').matches) contrast = 'increased';
        if (contrast === 'increased') document.documentElement.setAttribute('data-contrast', 'increased');
    })();
</script>

{{ Statamic::cpViteScripts() }}

@if (Statamic::pro() && config('statamic.cp.custom_css_url'))
    <link href="{{ config('statamic.cp.custom_css_url') }}?v={{ Statamic::version() }}" rel="stylesheet" />
@endif

@foreach (Statamic::availableExternalStyles(request()) as $url)
    <link href="{{ $url }}" rel="stylesheet" />
@endforeach

@foreach (Statamic::availableStyles(request()) as $package => $paths)
    @foreach ($paths as $path)
        <link href="{{ Statamic::vendorPackageAssetUrl($package, $path, 'css') }}" rel="stylesheet" />
    @endforeach
@endforeach

<style>
    :root {
        {{ \Statamic\CP\Color::cssVariables() }}

        &.dark {
            {{ \Statamic\CP\Color::cssVariables(dark: true) }}
        }
    }
</style>

@stack('head')
