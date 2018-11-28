@extends('statamic::layout')

@section('content')

    <form method="POST" action="{{ cp_route('globals.store') }}">
        @csrf

        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <a href="{{ cp_route('globals.index') }}">{{ __('Globals') }}</a>
                @svg('chevron-right')
                {{ __('Create Global Set') }}
            </h1>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>

        <div class="publish-fields card p-0">

            <form-group
                handle="title"
                display="{{ __('Title') }}"
                instructions="{{ __s('global_set_title_instructions') }}"
                value="{{ old('title') }}"
                error="{{ $errors->first('title') }}"
                autofocus
            ></form-group>

            <form-group
                handle="handle"
                display="{{ __('Handle') }}"
                instructions="{{ __s('global_set_handle_instructions') }}"
                value="{{ old('handle') }}"
                error="{{ $errors->first('handle') }}"
            ></form-group>

            <form-group
                handle="blueprint"
                display="{{ __('Blueprint') }}"
                instructions="{{ __s('global_set_blueprint_instructions') }}"
                value="{{ old('blueprint') }}"
                error="{{ $errors->first('blueprint') }}"
            ></form-group>

        </div>
    </form>

@endsection
