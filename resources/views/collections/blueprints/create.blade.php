@extends('statamic::layout')
@section('title', Statamic\trans('Create Blueprint'))

@section('content')
<form action="{{ $action }}" method="POST">
    @csrf
    <div class="max-w-lg mt-4 mx-auto">
        <div class="rounded p-6 lg:px-20 lg:py-10 shadow bg-white">
            <header class="text-center mb-16">
                <h1 class="mb-6">{{ Statamic\trans('Create Blueprint') }}</h1>
                <p class="text-gray">{{ Statamic\trans('statamic::messages.blueprints_intro') }}</p>
            </header>
            <div class="mb-10">
                <label class="font-bold text-base mb-1" for="name">{{ Statamic\trans('Title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" class="input-text" autofocus required tabindex="1">
                <div class="text-2xs text-gray-600 mt-2 flex items-center">
                    {{ Statamic\trans('statamic::messages.blueprints_title_instructions') }}
                </div>
                @if ($errors->has('title'))
                    <div class="text-red-500 text-xs mt-2">{{ $errors->first('title') }}</div>
                @endif
            </div>
        </div>

        <div class="flex justify-center mt-8">
            <button tabindex="4" class="btn-primary mx-auto btn-lg">
                {{ Statamic\trans('Create Blueprint') }}
            </button>
        </div>
    </div>
</form>
@stop
