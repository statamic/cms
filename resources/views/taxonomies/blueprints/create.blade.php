@extends('statamic::layout')
@section('title', __('Create Blueprint'))

@section('content')
<form action="{{ $action }}" method="POST">
    @csrf
    <div class="max-w-lg mt-4 mx-auto">
        <div class="rounded p-6 lg:px-20 lg:py-10 shadow bg-white">
            <header class="text-center mb-16">
                <h1 class="mb-6">{{ __('Create Blueprint') }}</h1>
                <p class="text-gray">{{ __('statamic::messages.blueprints_intro') }}</p>
            </header>
            <div class="mb-10">
                <label class="font-bold text-base mb-1" for="name">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" class="input-text" autofocus required tabindex="1">
                <div class="text-2xs text-gray-600 mt-2 flex items-center">
                    {{ __('statamic::messages.blueprints_title_instructions') }}
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <button tabindex="4" class="btn-primary mx-auto btn-lg">
                {{ __('Create Blueprint') }}
            </button>
        </div>
    </div>
</form>
@stop
