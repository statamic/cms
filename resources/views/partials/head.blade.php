@php use function Statamic\trans as __; @endphp

<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta name="robots" content="noindex,nofollow">

<title>@yield('title', $title ?? __('Here')) {{ Statamic::cpDirection() === 'ltr' ? '‹' : '›' }} {{ __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic') }}</title>

@if (Statamic::pro() && config('statamic.cp.custom_favicon_url'))
    @include('statamic::partials.favicon', ['favicon_url' => config('statamic.cp.custom_favicon_url')])
@else
    <link rel="icon" type="image/png" href="{{ Statamic::cpViteAsset('img/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" type="image/png" href="{{ Statamic::cpViteAsset('img/favicon-16x16.png') }}" sizes="16x16" />
    <link rel="shortcut icon" type="image/x-icon" href="{{ Statamic::cpViteAsset('img/favicon.ico') }}" sizes="16x16 32x32"/>
@endif

<script>
    (function () {
        let theme = {!! ($userTheme = $user?->preferredTheme()) ? "'".$userTheme."'" : "null" !!};
        if (! theme) theme = localStorage.getItem('statamic.theme') ?? 'auto';
        if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) theme = 'dark';
        if (theme === 'dark') document.documentElement.classList.add('dark');
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
