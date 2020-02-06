@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))

@section('content')

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            <small class="subhead block">
                <a href="{{ cp_route('taxonomies.index')}}">{{ __('Taxonomies') }}</a>
            </small>
            {{ $taxonomy->title() }}
        </h1>
        <dropdown-list class="mr-1">
            <dropdown-item :text="__('Delete Taxonomy')" class="warning"></dropdown-item>
            @can('edit', $taxonomy)
                <dropdown-item :text="__('Edit Taxonomy')" redirect="{{ $taxonomy->editUrl() }}"></dropdown-item>
            @endcan
        </dropdown-list>
        @can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy])
            <create-term-button
                url="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site->handle()]) }}"
                :blueprints="{{ $blueprints->toJson() }}">
            </create-term-button>
        @endcan
    </div>

    @if ($hasTerms)

        <term-list
            taxonomy="{{ $taxonomy->handle() }}"
            initial-sort-column="{{ $taxonomy->sortField() }}"
            initial-sort-direction="{{ $taxonomy->sortDirection() }}"
            :filters="{{ $filters->toJson() }}"
            action-url="{{ cp_route('taxonomies.terms.actions', $taxonomy->handle()) }}"
        ></term-list>

    @else

        @component('statamic::partials.create-first', [
            'resource' => __("{$taxonomy->title()} term"),
            'svg' => 'empty/taxonomy', // TODO: Do we want separate term SVG?
            'can' => $user->can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy])
        ])
            @slot('button')
                {{-- <create-term-button
                    url="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site->handle()]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                </create-term-button> --}}
            @endslot
        @endcomponent

    @endif

@endsection
