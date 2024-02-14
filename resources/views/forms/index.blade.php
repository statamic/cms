@extends('statamic::layout')
@section('title', Statamic\trans('Forms'))

@section('content')

    @unless($forms->isEmpty())

        <div class="flex items-center mb-6">
            <h1 class="flex-1">{{ Statamic\trans('Forms') }}</h1>

            @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Forms\Form'))
                <a href="{{ cp_route('forms.create') }}" class="btn-primary">{{ Statamic\trans('Create Form') }}</a>
            @endif
        </div>

        <form-listing
            :initial-columns="{{ json_encode($initialColumns) }}"
            action-url="{{ $actionUrl }}"
        ></form-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => Statamic\trans('Forms'),
            'description' => Statamic\trans('statamic::messages.form_configure_intro'),
            'svg' => 'empty/form',
            'button_text' => Statamic\trans('Create Form'),
            'button_url' => cp_route('forms.create'),
            'can' => $user->can('create', 'Statamic\Contracts\Forms\Form')
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Forms'),
        'url' => Statamic::docsUrl('forms')
    ])

@endsection
