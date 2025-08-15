<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
>
    <head>
        @include('statamic::partials.head')
    </head>

    <body class="bg-gray-50 font-sans leading-normal scheme-light p-2">
        <config-provider>
            <div id="statamic">
                @yield('content')
                <portal-targets></portal-targets>
            </div>
            @include('statamic::partials.scripts')
            @yield('scripts')
        </config-provider>
    </body>
</html>
