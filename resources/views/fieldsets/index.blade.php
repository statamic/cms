@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Fieldsets'))

@section('content')
    @unless ($fieldsets->isEmpty())
        <ui-header title="{{ __('Fieldsets') }}" icon="fieldsets">
            <ui-command-palette-item
                category="{{ Statamic\CommandPalette\Category::Actions }}"
                text="{{ __('Create Fieldset') }}"
                icon="fieldsets"
                url="{{ cp_route('fieldsets.create') }}"
                v-slot="{ text, url }"
            >
                <ui-button
                    :href="url"
                    :text="text"
                    variant="primary"
                ></ui-button>
            </ui-command-palette-item>
        </ui-header>

        @foreach ($fieldsets as $key => $f)
            <div class="mb-4">
                @if ($fieldsets->count() > 1)
                    <h3 class="little-heading mb-2 ps-0">{{ $key }}</h3>
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

    <x-statamic::docs-callout
        :topic="__('Blueprints')"
        :url="Statamic::docsUrl('blueprints')"
    />
@endsection
