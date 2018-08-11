<!doctype html>
<html lang="en"> {{-- TODO: Set lang with current translation --}}
    <head>
        @include('statamic::partials.head')
    </head>
    <body class="outside @yield('body_class')">
        <div id="statamic">
            @yield('content')
        </div>
        {{-- <script>Statamic.translations = {!! $translations !!};</script> --}}
        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
