<!doctype html>
<html lang="en">
    <head>
        @include('statamic::partials.head')
    </head>
    <body class="outside">
        <div id="statamic">
            @yield('content')
        </div>
        {{-- <script>Statamic.translations = {!! $translations !!};</script> --}}
        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
