@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))

@section('content')

    <div class="flex items-center mb-3">
        <h1 class="flex-1">
            <small class="subhead block">
                <a href="{{ cp_route('collections.index')}}">{{ __('Collections') }}</a>
            </small>
            {{ $collection->title() }}
        </h1>
        <dropdown-list class="mr-1">
            @can('edit', $collection)
                <dropdown-item :text="__('Edit Collection')" redirect="{{ $collection->editUrl() }}"></dropdown-item>
            @endcan
            @can('delete', $collection)
                <dropdown-item :text="__('Delete Collection')" class="warning" @click="$refs.deleter.confirm()">
                    <resource-deleter
                        ref="deleter"
                        :resource-title="__('Collection')"
                        route="{{ cp_route('collections.destroy', $collection->handle()) }}"
                        redirect="{{ cp_route('collections.index') }}"
                    ></resource-deleter>
                </dropdown-item>
            @endcan
        </dropdown-list>
        @can('create', ['Statamic\Contracts\Entries\Entry', $collection])
            <create-entry-button
                url="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}"
                :blueprints="{{ $blueprints->toJson() }}">
            </create-entry-button>
        @endcan
    </div>

    @if ($collection->queryEntries()->count())

        <entry-list
            collection="{{ $collection->handle() }}"
            initial-sort-column="{{ $collection->sortField() }}"
            initial-sort-direction="{{ $collection->sortDirection() }}"
            :filters="{{ $filters->toJson() }}"
            action-url="{{ cp_route('collections.entries.actions', $collection->handle()) }}"
            :reorderable="{{ Statamic\Support\Str::bool($collection->orderable() && $user->can('reorder', $collection)) }}"
            reorder-url="{{ cp_route('collections.entries.reorder', $collection->handle()) }}"
            structure-url="{{ optional($collection->structure())->showUrl() }}"
        ></entry-list>

    @else

        @component('statamic::partials.create-first', [
            'resource' => __("{$collection->title()} entry"),
            'svg' => 'empty/collection', // TODO: Do we want separate entry SVG?
            'can' => $user->can('create', ['Statamic\Contracts\Entries\Entry', $collection])
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
