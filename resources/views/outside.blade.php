<!doctype html>
<html lang="{{ config('app.locale') }}" dir="{{ Statamic\Facades\Site::current()->direction ?? 'rtl' }}">
    <head>
        @include('statamic::partials.head')
    </head>
    <body class="outside {{ config('statamic.cp.theme') }}-theme @yield('body_class')">
        <div id="statamic" class="statamic-outside">
            @yield('content')
        </div>
        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
