<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" dir="{{ Statamic\Facades\Site::current()->direction ?? 'rtl' }}">
<head>
    @include('statamic::partials.head')
</head>
<body class="outside {{ config('statamic.cp.theme') }}-theme @yield('body_class')">
<div id="statamic">
    <div id="main" class="flex justify-center">
        <div>
            @yield('content')
        </div>
    </div>

    <portal-targets></portal-targets>
</div>
@include('statamic::partials.scripts')
@yield('scripts')
</body>
</html>
