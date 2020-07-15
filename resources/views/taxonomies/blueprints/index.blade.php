@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('taxonomies.show', $taxonomy->handle()),
        'title' => $taxonomy->title()
    ])

    <div class="flex justify-between items-center mb-3">
        <h1>@yield('title')</h1>

        @can('create', 'Statamic\Fields\Blueprint')
            <a href="{{ cp_route('taxonomies.blueprints.create', $taxonomy) }}" class="btn-primary">{{ __('Create Blueprint') }}</a>
        @endcan
    </div>

    <blueprint-listing :initial-rows="{{ json_encode($blueprints) }}"></blueprint-listing>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
