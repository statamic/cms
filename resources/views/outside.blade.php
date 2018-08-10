<!doctype html>
<html lang="en">
    <head>
        @include('statamic::partials.head')
    </head>
    <body class="outside">
        <div id="statamic">
            <div class="logo">{!! inline_svg('statamic-mark') !!}</div>
            <div class="box card mx-auto" @yield('box-attributes')>
                <div id="wrapper">
                    @yield('title')
                    @include('statamic::partials.flash')
                    @yield('content')
                </div>
            </div>
        </div>
        {{-- <script>Statamic.translations = {!! $translations !!};</script> --}}
        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
