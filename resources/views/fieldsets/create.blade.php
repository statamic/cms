@extends('statamic::layout')
@section('title', __('Create Fieldset'))

@section('content')

    <form action="{{ cp_route('fieldsets.store') }}" method="POST">
        @csrf

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Create Fieldset') }}</h1>
        </div>

        <div class="card p-0 mb-3">
            <div class="publish-fields">
                <form-group
                    handle="title"
                    :display="__('Title')"
                    :instructions="__('messages.fieldsets_title_instructions')"
                    autofocus />
            </div>
        </div>

        <div class="flex items-center">
            <button class="btn btn-primary">{{ __('Create') }}</button>
            <p class="text-xs text-grey-60 ml-2">You will be able to customize your Fieldset once it's created.</p>
        </div>

    </form>

@endsection
