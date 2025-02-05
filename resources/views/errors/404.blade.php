@php
    use function Statamic\trans as __;
    $authed = \Statamic\Facades\User::current();
@endphp

@extends($authed ? 'statamic::layout' : 'statamic::outside')

@section('content')
    <div class="{{ $authed ? 'pt-30' : 'items-center' }} flex min-h-screen justify-center">
        <h1 class="mb-10 text-center text-3xl tracking-tighter opacity-50">{{ __('Page Not Found') }}</h1>
    </div>
@endsection
