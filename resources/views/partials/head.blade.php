<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', $title ?? __('Here'))</title>
<link rel="icon" type="image/png" href="{{ cp_resource_url('img/favicon@2x.png') }}" sizes="32x32" />
<link rel="icon" type="image/png" href="{{ cp_resource_url('img/favicon.png') }}" sizes="16x16" />
<link href="{{ cp_resource_url('css/cp.css') }}?v={{ Statamic::version() }}" rel="stylesheet" />

@foreach (Statamic::availableStyles(request()) as $name => $path)
<link href="{{ resource_url("vendor/$name/css/$path") }}" rel="stylesheet" />
@endforeach

@stack('head')
