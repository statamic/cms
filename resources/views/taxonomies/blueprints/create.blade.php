@extends('statamic::layout')
@section('title', __('Create Blueprint'))

@section('content')
<form action="{{ $action }}" method="POST">
    @csrf
    <div class="max-w-lg mt-2 mx-auto">
        <div class="rounded p-3 lg:px-7 lg:py-5 shadow bg-white">
            <header class="text-center mb-16___REPLACED">
                <h1 class="mb-6___REPLACED">{{ __('Create Blueprint') }}</h1>
                <p class="text-grey">{{ __('statamic::messages.blueprints_intro') }}</p>
            </header>
            <div class="mb-10___REPLACED">
                <label class="font-bold text-base mb-1___REPLACED" for="name">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" class="input-text" autofocus required tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    {{ __('statamic::messages.blueprints_title_instructions') }}
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-8___REPLACED">
            <button tabindex="4" class="btn-primary mx-auto btn-lg">
                {{ __('Create Blueprint') }}
            </button>
        </div>
    </div>
</form>
@stop
