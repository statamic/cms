<!doctype html>
<html lang="en">
    <head>
        @include('statamic::partials.head')
    </head>
    <body class="outside">
        <div id="statamic">
            <div class="logo">{!! inline_svg('statamic-mark') !!}</div>
                @yield('content')
            </div>
        </div>
        {{-- <script>Statamic.translations = {!! $translations !!};</script> --}}
        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
