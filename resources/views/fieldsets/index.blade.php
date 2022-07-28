@extends('statamic::layout')
@section('title', __('Fieldsets'))

@section('content')

    @unless($fieldsets->isEmpty())

        <header class="mb-3">
            <div class="flex flex-wrap items-center max-w-full gap-2">
                <h1 class="flex-1 break-words max-w-full">{{ __('Fieldsets') }}</h1>

                <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary">{{ __('Create Fieldset') }}</a>
            </div>
        </header>

        <fieldset-listing
            :initial-rows="{{ json_encode($fieldsets) }}">
        </fieldset-listing>

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
