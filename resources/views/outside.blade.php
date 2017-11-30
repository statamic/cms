<!doctype html>
<html lang="en">
    <head>
        @include('partials.head')
    </head>

    <body id="statamic" class="outside">

        <div class="container">
            <div class="row">
                <div class="logo">{!! inline_svg('statamic-mark') !!}</div>
                <div class="box card col-centered">
                    <div id="wrapper">

                        @yield('title')

                        @include('partials.flash')

                        @yield('content')

                    </div>
                </div>
            </div>
        </div>

     </div>
        <script>Statamic.translations = {!! $translations !!};</script>
        @include('partials.scripts')
        @yield('scripts')
    </body>
</html>
