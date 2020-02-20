@extends('statamic::outside')
@section('body_class', 'rad-mode')
@section('title', __('Unauthorized'))

@section('content')
<div class="logo pt-7">
    @svg('statamic-wordmark')
</div>

<div class="card auth-card mx-auto text-center text-grey-70">
    <div class="mb-3">{{ __('Unauthorized') }}</div>

    <a class="btn-primary" href="{{ cp_route('logout') }}?redirect={{ cp_route('login') }}">{{ __('Log out') }}</a>
</div>

@endsection
