@extends('statamic::layout')

@section('content')

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ __('Forms') }}</h1>
            @can('super')
                <a href="{{ cp_route('forms.create') }}" class="btn btn-primary">{{ __('Create Form') }}</a>
            @endcan
        </div>

        @if(count($forms) == 0)
        <div class="card">
            <div class="no-results">
                <span class="icon icon-download"></span>
                <h2>{{ __('Forms') }}</h2>
                <h3>{{ __('Forms collect, display, and report user submitted responses.') }}</h3>
                @can('super')
                    <a href="{{ cp_route('forms.create') }}" class="btn btn-default btn-lg">{{ __('Create Form') }}</a>
                @endcan
            </div>
        </div>
        @endif

    @if(count($forms) > 0)
        <form-listing :forms="{{ $forms }}"></form-listing>
    @endif

@endsection
