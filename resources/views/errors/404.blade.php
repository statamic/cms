@php
    use function Statamic\trans as __;
    $authed = \Statamic\Facades\User::current();
@endphp

@extends($authed ? 'statamic::layout' : 'statamic::outside')

@section('content')
    <div class="flex min-h-screen flex-col items-center justify-center">
        <ui-card class="text-center flex flex-col items-center space-y-2">
            <ui-heading size="2xl">{{ __('404') }}</ui-heading>
            <ui-description>{{ __('The page you are looking for could not be found.') }}</ui-description>
        </ui-card>
    </div>
@endsection
