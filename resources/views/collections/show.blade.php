@extends('statamic::layout')

@section('content')

    @if($collection->queryEntries()->count())

        <div class="flex items-center mb-3">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a href="{{ cp_route('collections.index')}}">{{ __('Collections') }}</a>
                </small>
                {{ $collection->title() }}
            </h1>
            <dropdown-list class="mr-2">
                <ul class="dropdown-menu">
                    <li><a href="{{ $collection->editUrl() }}">{{ __('Edit Collection') }}</a></li>
                    <li class="warning"><a href="#">{{ __('Delete Collection') }}</a></li>
                </ul>
            </dropdown-list>
            @can('create', ['Statamic\Contracts\Data\Entries\Entry', $collection])
                <create-entry-button
                    url="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                </create-entry-button>
            @endcan
        </div>

        <entry-list
            collection="{{ $collection->handle() }}"
            initial-sort-column="{{ $collection->sortField() }}"
            initial-sort-direction="{{ $collection->sortDirection() }}"
            :filters="{{ $filters->toJson() }}"
            :actions="{{ $actions->toJson() }}"
            action-url="{{ cp_route('collections.entries.action', $collection->handle()) }}"
        ></entry-list>

    @else

        @component('statamic::partials.create-first', [
            'resource' => __("{$collection->title()} Entry"),
            'svg' => 'empty/collection', // TODO: Do we want separate entry SVG?
            'can' => user()->can('create', ['Statamic\Contracts\Data\Entries\Entry', $collection])
        ])
            @slot('button')
                <create-entry-button
                    url="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}"
                    :blueprints="{{ $blueprints->toJson() }}">
                </create-entry-button>
            @endslot
        @endcomponent

    @endif

@endsection
