@extends('statamic::layout')

@section('content')

    @unless($forms->isEmpty())

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ __('Forms') }}</h1>
            <a href="{{ cp_route('forms.create') }}" class="btn-primary">{{ __('Create Form') }}</a>
        </div>

        <form-listing :forms="{{ json_encode($forms) }}"></form-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'Form',
            'description' => 'Forms are used to collect information from your visitors and dispatch notifications to you and your team of new submissions',
            'svg' => 'empty/form',
            'route' => cp_route('forms.create')
        ])

    @endunless

@endsection
