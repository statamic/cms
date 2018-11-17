@extends('statamic::layout')

@section('content')

    @if(count($forms) == 0)
        <div class="text-center max-w-sm mx-auto pt-8 screen-centered">
            @svg('empty/form')
            <h1 class="my-3">{{ __('Create your first Form now') }}</h1>
            <p class="text-grey mb-3">
                {{ __('Forms are used to collect information from your visitors and dispatch notifications to you and your team of new submissions') }}
            </p>
            @can('super')
                <a href="{{ cp_route('forms.create') }}" class="btn-primary btn-lg">{{ __('Create Form') }}</a>
            @endcan
        </div>
    @endif

    @if(count($forms) > 0)
        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ __('Forms') }}</h1>
            @can('super')
                <a href="{{ cp_route('forms.create') }}" class="btn btn-primary">{{ __('Create Form') }}</a>
            @endcan
        </div>

        <form-listing :forms="{{ json_encode($forms) }}"></form-listing>
    @endif

@endsection
