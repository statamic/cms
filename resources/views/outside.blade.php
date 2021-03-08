<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        @include('statamic::partials.head')
    </head>
    <body class="outside {{ config('statamic.cp.theme') }}-theme @yield('body_class')">
        <div id="statamic">
            @yield('content')
        </div>
        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
