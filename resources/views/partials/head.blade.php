<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', $title ?? __('Here')) ‹ Statamic</title>
<link rel="icon" type="image/png" href="{{ Statamic::assetUrl('img/favicon-32x32.png') }}" sizes="32x32" />
<link rel="icon" type="image/png" href="{{ Statamic::assetUrl('img/favicon-16x16.png') }}" sizes="16x16" />
<link rel="shortcut icon" type="image/x-icon" href="{{ Statamic::assetUrl('img/favicon.ico') }}" sizes="16x16 32x32"/>
<link href="{{ Statamic::assetUrl('css/cp.css') }}?v={{ Statamic::version() }}" rel="stylesheet" />

@foreach (Statamic::availableStyles(request()) as $name => $path)
<link href="{{ Statamic::url("vendor/$name/css/$path") }}" rel="stylesheet" />
@endforeach

@stack('head')
