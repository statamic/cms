@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('collections.show', $collection->handle()),
        'title' => $collection->title()
    ])

    <div class="flex justify-between items-center mb-6">
        <h1>@yield('title')</h1>

        <div>
            @if ($blueprints->count() > 1)
                <button
                        class="btn"
                        :class="{ 'disabled': !hasBeenReordered }"
                        :disabled="!hasBeenReordered"
                        @click="saveOrder"
                >{{ __('Save Order') }}</button>
            @endif

            <a href="{{ cp_route('collections.blueprints.create', $collection) }}" class="btn-primary rtl:mr-2 ltr:ml-2">{{ __('Create Blueprint') }}</a>
        </div>
    </div>

    <collection-blueprint-listing
        :initial-rows="{{ json_encode($blueprints) }}"
        reorder-url="{{ cp_route('collections.blueprints.reorder', $collection) }}"
    ></collection-blueprint-listing>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
