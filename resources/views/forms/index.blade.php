@extends('statamic::layout')
@section('title', __('Forms'))

@section('content')

    @unless($forms->isEmpty())

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ __('Forms') }}</h1>

            @can('create', 'Statamic\Contracts\Forms\Form')
                <a href="{{ cp_route('forms.create') }}" class="btn-primary">{{ __('Create Form') }}</a>
            @endcan
        </div>

        <form-listing :forms="{{ json_encode($forms) }}"></form-listing>

    @else

        @include('statamic::partials.empty-state', [
            'resource' => 'Form',
            'description' => __('statamic::messages.form_configure_intro'),
            'svg' => 'empty/form',
            'route' => cp_route('forms.create')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Forms'),
        'url' => 'forms'
    ])

@endsection
