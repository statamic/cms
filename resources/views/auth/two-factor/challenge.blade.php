@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Two Factor Authentication'))

@section('content')
    @include('statamic::partials.outside-logo')

    <div class="relative mx-auto flex max-w-xs items-center justify-center rounded shadow-lg">
        <div class="outside-shadow absolute inset-0"></div>
        <div class="card auth-card">
            <two-factor-challenge
                initial-mode="{{ $mode }}"
                :errors="{{ $errors->isEmpty() ? '{}' : json_encode($errors->getMessages()) }}"
                csrf-token="{{ csrf_token() }}"
                redirect="{{ request('redirect') }}"
            ></two-factor-challenge>
        </div>
    </div>
@endsection
