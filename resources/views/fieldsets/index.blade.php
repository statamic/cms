@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Fieldsets'))

@section('content')
    @unless ($fieldsets->isEmpty())
        <ui-header title="{{ __('Fieldsets') }}">
            <ui-button
                href="{{ cp_route('fieldsets.create') }}"
                text="{{ __('Create Fieldset') }}"
                variant="primary"
            ></ui-button>
        </ui-header>

        @foreach ($fieldsets as $key => $f)
            <div class="mb-4">
                @if ($fieldsets->count() > 1)
                    <h3 class="little-heading mb-2 ltr:pl-0 rtl:pr-0">{{ $key }}</h3>
                @endif

                <fieldset-listing :initial-rows="{{ json_encode($f) }}"></fieldset-listing>
            </div>
        @endforeach
    @else
        @include(
            'statamic::partials.empty-state',
            [
                'title' => __('Fieldsets'),
                'description' => __('statamic::messages.fieldset_intro'),
                'svg' => 'empty/fieldsets',
                'button_text' => __('Create Fieldset'),
                'button_url' => cp_route('fieldsets.create'),
            ]
        )
    @endunless

    @include(
        'statamic::partials.docs-callout',
        [
            'topic' => __('Fieldsets'),
            'url' => Statamic::docsUrl('fieldsets'),
        ]
    )
@endsection
