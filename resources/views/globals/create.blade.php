@extends('statamic::layout')
@section('title', __('Create Global Set'))
@section('content')

    <global-create-form
        action="{{ cp_route('globals.store') }}"
    >
        <h1 slot="header" class="flex-1">
            <small class="subhead block">
                <a href="{{ cp_route('globals.index') }}" class="text-grey hover:text-blue">{{ __('Globals') }}</a>
            </small>
            {{ __('Create Global Set') }}
        </h1>
    </global-create-form>

@endsection
