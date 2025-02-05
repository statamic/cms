@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Fieldsets'))

@section('content')
    @unless ($fieldsets->isEmpty())
        <div class="mb-6 flex">
            <h1 class="flex-1">{{ __('Fieldsets') }}</h1>
            <a href="{{ cp_route('fieldsets.create') }}" class="btn-primary">{{ __('Create Fieldset') }}</a>
        </div>

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
