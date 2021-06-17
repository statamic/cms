@extends('statamic::layout')
@section('title', __('Fieldsets'))

@section('content')

    @unless($fieldsets->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Fieldsets') }}</h1>
            <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary">{{ __('Create Fieldset') }}</a>
        </div>

        <fieldset-listing :initial-rows="{{ json_encode($fieldsets) }}"></fieldset-listing>

    @else

        @include('statamic::partials.empty-state', [
            'title' => __('Fieldsets'),
            'description' => __('statamic::messages.fieldset_intro'),
            'svg' => 'empty/form',
            'button_text' => __('Create Fieldset'),
            'button_url' => cp_route('fieldsets.create'),
        ])

    @endunless

    @include('statamic::partials.docs-callout', [
        'topic' => __('Fieldsets'),
        'url' => Statamic::docsUrl('fieldsets')
    ])

@endsection
