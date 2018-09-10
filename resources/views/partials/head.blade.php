<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta name="robots" content="noindex,nofollow">
<title>@yield('title', $title ?? __('Here')) &#10174; Statamic</title>
<link rel="icon" type="image/png" href="{{ cp_resource_url('img/favicon@2x.png') }}" sizes="32x32" />
<link rel="icon" type="image/png" href="{{ cp_resource_url('img/favicon.png') }}" sizes="16x16" />
<link href="{{ cp_resource_url('css/cp.css') }}?v={{ STATAMIC_VERSION }}" rel="stylesheet" />

@foreach (Statamic::availableStyles(request()) as $name => $path)
<link href="{{ resource_url("vendor/$name/css/$path") }}" rel="stylesheet" />
@endforeach

<script>
    window.Statamic =  {!! json_encode([
        'csrfToken' => csrf_token(),
        'siteRoot' => site_root(),
        'cpRoot' => cp_root(),
        'urlPath' => '/' . request()->path(),
        'resourceUrl' => cp_resource_url('/'),
        'locales' => Statamic\API\Config::get('statamic.system.locales'),
        'markdownHardWrap' => Statamic\API\Config::get('statamic.theming.markdown_hard_wrap'),
        'conditions' => [],
        'MediumEditorExtensions' => [],
        'flash' => []
    ]) !!};
</script>

@stack('head')
