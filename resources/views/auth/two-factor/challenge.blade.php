@php
    use function Statamic\trans as __;
@endphp

@inject('str', 'Statamic\Support\Str')
@extends('statamic::outside')
@section('title', __('Two-Factor Authentication'))

@section('content')
    <div class="relative mx-auto max-w-[400px] items-center justify-center pt-20">
        <div class="flex items-center justify-center py-6">
            <x-statamic::outside-logo />
        </div>
        <two-factor-challenge
            initial-mode="{{ $mode }}"
            :errors="{{ $errors->isEmpty() ? '{}' : json_encode($errors->getMessages()) }}"
            csrf-token="{{ csrf_token() }}"
            redirect="{{ request('redirect') }}"
        ></two-factor-challenge>
    </div>
@endsection
