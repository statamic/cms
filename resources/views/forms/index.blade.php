@extends('statamic::layout')
@section('title', __('Forms'))

@section('content')

    @unless($forms->isEmpty())

        <header class="flex items-center mb-3">
            <h1 class="flex-1 break-words max-w-full">{{ __('Forms') }}</h1>

            @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Forms\Form'))
                <a href="{{ cp_route('forms.create') }}" class="btn-primary">{{ __('Create Form') }}</a>
            @endif
        </header>

        <form-listing
            :initial-columns="{{ json_encode($initialColumns) }}"
            action-url="{{ $actionUrl }}"
        ></form-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Forms'),
            'description' => __('statamic::messages.form_configure_intro'),
            'svg' => 'empty/form',
            'button_text' => __('Create Form'),
            'button_url' => cp_route('forms.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Forms\Form')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Forms'),
        'url' => Statamic::docsUrl('forms')
    ])

@endsection
