@php
    use function Statamic\trans as __;
@endphp

<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width" />
<meta name="robots" content="noindex,nofollow" />

<title>
    @yield('title', $title ?? __('Here')) {{ Statamic::cpDirection() === 'ltr' ? '‹' : '›' }}
    {{ __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic') }}
</title>

@if (Statamic::pro() && config('statamic.cp.custom_favicon_url'))
    @include('statamic::partials.favicon', ['favicon_url' => config('statamic.cp.custom_favicon_url')])
@else
    <link rel="icon" href="{{ Statamic::cpViteAsset('img/favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ Statamic::cpViteAsset('img/apple-touch-icon.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ Statamic::cpViteAsset('img/favicon.ico') }}">
@endif

<script>
    (function () {
        let theme = {!! ($userTheme = $user?->preferredTheme()) ? "'" . $userTheme . "'" : 'null' !!};
        if (!theme) theme = localStorage.getItem('statamic.theme') ?? 'auto';
        if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) theme = 'dark';
        if (theme === 'dark') document.documentElement.classList.add('dark');
        
        let contrast = {!! ($userContrast = $user?->preferences()['contrast'] ?? null) ? "'" . $userContrast . "'" : 'null' !!};
        if (!contrast) contrast = localStorage.getItem('statamic.contrast') ?? 'default';
        if (contrast === 'auto' && window.matchMedia('(prefers-contrast: more)').matches) contrast = 'more';
        if (contrast === 'more') document.documentElement.classList.add('contrast-more');
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

@stack('head')
