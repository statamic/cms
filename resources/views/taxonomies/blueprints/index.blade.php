@extends('statamic::layout')
@section('title', Statamic\trans('Blueprints'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('taxonomies.show', $taxonomy->handle()),
        'title' => $taxonomy->title()
    ])

    <taxonomy-blueprint-listing
        inline-template
        :initial-rows="{{ json_encode($blueprints) }}"
        reorder-url="{{ cp_route('taxonomies.blueprints.reorder', $taxonomy) }}"
    >
        <div>
            <div class="flex justify-between items-center mb-6">
                <h1>@yield('title')</h1>

                <div>
                    @if ($blueprints->count() > 1)
                        <button
                            class="btn"
                            :class="{ 'disabled': !hasBeenReordered }"
                            :disabled="!hasBeenReordered"
                            @click="saveOrder"
                        >{{ Statamic\trans('Save Order') }}</button>
                    @endif

                    <a href="{{ cp_route('taxonomies.blueprints.create', $taxonomy) }}" class="btn-primary ml-2">{{ Statamic\trans('Create Blueprint') }}</a>
                </div>
            </div>

            <blueprint-listing
                :initial-rows="rows"
                :reorderable="{{ $blueprints->count() > 1 ? 'true' : 'false' }}"
                @reordered="reordered"
            ></blueprint-listing>
        </div>
    </taxonomy-blueprint-listing>

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
