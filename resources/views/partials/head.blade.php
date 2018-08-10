<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">
<meta id="csrf-token" value="{{ csrf_token() }}" />
<meta name="robots" content="noindex,nofollow">
<title>{{ $title or '' }} | Statamic</title>
<link href="{{ cp_resource_url('css/cp.css') }}?v={{ STATAMIC_VERSION }}" rel="stylesheet" />
@if (\Statamic\API\File::exists('site/helpers/cp/override.css'))
    <link href="{{ resource_url('helpers/cp/override.css') }}" rel="stylesheet" />
@endif
<link rel="icon" type="image/png" href="{{ cp_resource_url('img/favicon@2x.png') }}" sizes="32x32" />
<link rel="icon" type="image/png" href="{{ cp_resource_url('img/favicon.png') }}" sizes="16x16" />
<script>
    var Statamic = {
        'siteRoot': '{!! SITE_ROOT !!}',
        'cpRoot': '{!! $cp_root !!}',
        'urlPath': '/{!! request()->path() !!}',
        'resourceUrl': '{!! cp_resource_url('/') !!}',
        'locales': {!! json_encode(Statamic\API\Config::get('system.locales')) !!},
        'markdownHardWrap': {{ bool_str(Statamic\API\Config::get('theming.markdown_hard_wrap')) }},
        'conditions': {},
        'MediumEditorExtensions': {},
        'flash': [],
        'staticCachingEnabled': {{ \Statamic\API\Config::get('caching.static_caching_enabled') ? 'true' : 'false' }},
        'userId': '{{ \Statamic\API\User::loggedIn() ? \Statamic\API\User::getCurrent()->id() : null }}',
        'dateFormat': '{{ to_moment_js_date_format(\Statamic\API\Config::get('cp.date_format')) }}'
    };
</script>

{!! $layout_head !!}
