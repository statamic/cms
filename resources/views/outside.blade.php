<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
>
    <head>
        @include('statamic::partials.head')
    </head>

    <body class="bg-gray-50 font-sans leading-normal">
        <config-provider>
            <div id="statamic" v-cloak>
                @yield('content')
                <portal-targets></portal-targets>
            </div>
            @include('statamic::partials.scripts')
            @yield('scripts')
        </config-provider>
    </body>
</html>
