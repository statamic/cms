@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))
@section('wrapper_class', 'max-w-full')

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('taxonomies.index'),
            'title' => __('Taxonomies')
        ])
        <div class="flex items-center">
            <h1 class="flex-1">{{ $taxonomy->title() }}</h1>

            <dropdown-list class="mr-1">
                @can('edit', $taxonomy)
                    <dropdown-item :text="__('Edit Taxonomy')" redirect="{{ $taxonomy->editUrl() }}"></dropdown-item>
                @endcan
                @can('configure fields')
                    <dropdown-item :text="__('Edit Blueprints')" redirect="{{ cp_route('taxonomies.blueprints.index', $taxonomy) }}"></dropdown-item>
                @endcan
                @can('delete', $taxonomy)
                    <dropdown-item :text="__('Delete Taxonomy')" class="warning" @click="$refs.deleter.confirm()">
                        <resource-deleter
                            ref="deleter"
                            resource-title="{{ $taxonomy->title() }}"
                            route="{{ cp_route('taxonomies.destroy', $taxonomy->handle()) }}"
                            redirect="{{ cp_route('taxonomies.index') }}"
                        ></resource-deleter>
                    </dropdown-item>
                @endcan
            </dropdown-list>

            @can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy])
                <create-term-button
                    url="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                </create-term-button>
            @endcan
        </div>
    </header>

    @if ($hasTerms)

        <term-list
            taxonomy="{{ $taxonomy->handle() }}"
            initial-sort-column="{{ $taxonomy->sortField() }}"
            initial-sort-direction="{{ $taxonomy->sortDirection() }}"
            :initial-columns="{{ $columns->toJson() }}"
            :filters="{{ $filters->toJson() }}"
            action-url="{{ cp_route('taxonomies.terms.actions.run', $taxonomy->handle()) }}"
        ></term-list>

    @else

        @component('statamic::partials.create-first', [
            'resource' => __("{$taxonomy->title()} term"),
            'svg' => 'empty/taxonomy', // TODO: Do we want separate term SVG?
            'can' => $user->can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy])
        ])
            @slot('button')
                {{-- <create-term-button
                    url="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                </create-term-button> --}}
            @endslot
        @endcomponent

    @endif

@endsection
