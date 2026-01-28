<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
>
    <head>
        @include('statamic::partials.head')
    </head>

    <body
        @if ($user && $user->getPreference('strict_accessibility')) data-contrast="increased" @endif
    >
        <div
            id="statamic"
            data-page="{{ json_encode($page ?? Statamic::nonInertiaPageData()) }}"
        >
            <div id="blade-title" data-title="
                @yield('title', $title ?? __('Here')) {{ Statamic::cpDirection() === 'ltr' ? '‹' : '›' }}
                {{ __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic') }}
            "></div>
            @yield('content')
        </div>

        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
