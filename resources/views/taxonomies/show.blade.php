@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))

@section('content')
    <ui-header title="{{ __($taxonomy->title()) }}">
        <ui-dropdown>
            <ui-dropdown-menu>
                @can('edit', $taxonomy)
                    <ui-dropdown-item :text="__('Configure Taxonomy')" icon="cog" href="{{ $taxonomy->editUrl() }}"></ui-dropdown-item>
                @endcan

                @can('configure fields')
                    <ui-dropdown-item
                        :text="__('Edit Blueprints')"
                        icon="blueprint-edit"
                        href="{{ cp_route('blueprints.taxonomies.index', $taxonomy) }}"
                    ></ui-dropdown-item>
                @endcan

                @can('delete', $taxonomy)
                    <ui-dropdown-item :text="__('Delete Taxonomy')" icon="trash" variant="destructive" @click="$refs.deleter.confirm()"></ui-dropdown-item>
                @endcan
            </ui-dropdown-menu>
        </ui-dropdown>

        @if ($canCreate)
            <create-term-button
                url="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}"
                text="{{ $taxonomy->createLabel() }}"
                :blueprints="{{ $blueprints->toJson() }}"
            ></create-term-button>
        @endif
    </ui-header>

    @can('delete', $taxonomy)
        <resource-deleter
            ref="deleter"
            resource-title="{{ $taxonomy->title() }}"
            route="{{ cp_route('taxonomies.destroy', $taxonomy->handle()) }}"
            redirect="{{ cp_route('taxonomies.index') }}"
        ></resource-deleter>
    @endcan

    @if ($hasTerms)
        <term-list
            taxonomy="{{ $taxonomy->handle() }}"
            sort-column="{{ $taxonomy->sortField() }}"
            sort-direction="{{ $taxonomy->sortDirection() }}"
            :columns="{{ $columns->toJson() }}"
            :filters="{{ $filters->toJson() }}"
            action-url="{{ cp_route('taxonomies.terms.actions.run', $taxonomy->handle()) }}"
        ></term-list>
    @else
        @component(
            'statamic::partials.create-first',
            [
                'resource' => __("{$taxonomy->title()} term"),
                'svg' => 'empty/taxonomy', // TODO: Do we want separate term SVG?
                'can' => $user->can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy]),
            ]
        )
            @slot('button')
                {{--
                    <create-term-button
                    url="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                    </create-term-button>
                --}}
            @endslot
        @endcomponent
    @endif
@endsection
