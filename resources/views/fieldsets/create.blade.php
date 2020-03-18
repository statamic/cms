@extends('statamic::layout')
@section('title', __('Create Fieldset'))

@section('content')
<form action="{{ cp_route('fieldsets.store') }}" method="POST">
    @csrf
    <div class="max-w-lg mt-2 mx-auto">
        <div class="rounded p-3 lg:px-7 lg:py-5 shadow bg-white">
            <header class="text-center mb-6">
                <h1 class="mb-3">{{ __('Create a Fieldset') }}</h1>
                <p class="text-grey">{{ __('statamic::messages.fields_fieldsets_description') }}</p>
            </header>
            <div class="mb-5">
                <label class="font-bold text-base mb-sm" for="name">{{ __('Title') }}</label>
                <input type="text" name="title" value="{{ old('title') }}" class="input-text" autofocus required tabindex="1">
                <div class="text-2xs text-grey-60 mt-1 flex items-center">
                    {{ __('statamic::messages.fieldsets_title_instructions') }}
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-4">
            <button tabindex="4" class="btn-primary mx-auto btn-lg">
                {{ __('Create Fieldset')}}
            </button>
        </div>
    </div>
</form>
@stop
