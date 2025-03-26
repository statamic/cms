@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Blueprint'))

@section('content')
<form action="{{ $action }}" method="POST">
    @csrf
    <div class="mx-auto mt-4 max-w-2xl">
        <div class="rounded-sm bg-white p-6 shadow dark:bg-dark-600 dark:shadow-lg lg:px-20 lg:py-10">
            <header class="mb-16 text-center">
                <h1 class="mb-6">{{ __('Create Blueprint') }}</h1>
                <p class="text-gray">{{ __('statamic::messages.blueprints_intro') }}</p>
            </header>
            <div class="mb-10">
                <label class="mb-1 text-base font-bold" for="name">{{ __('Title') }}</label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title') }}"
                    class="input-text"
                    autofocus
                    required
                    tabindex="1"
                />
                <div class="mt-2 flex items-center text-2xs text-gray-600">
                    {{ __('statamic::messages.blueprints_title_instructions') }}
                </div>
                @if ($errors->has('title'))
                    <div class="mt-2 text-xs text-red-500">{{ $errors->first('title') }}</div>
                @endif
            </div>
        </div>

        <div class="mt-8 flex justify-center">
            <button tabindex="4" class="btn-primary btn-lg mx-auto">
                {{ __('Create Blueprint') }}
            </button>
        </div>
    </div>
</form>
@stop
