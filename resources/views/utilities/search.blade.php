@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Rebuild Search'))

@section('content')

<ui-header title="{{ __('Search') }}" icon="magnifying-glass">
    <form method="POST" action="{{ cp_route('utilities.search', 'all') }}">
        @csrf

        @foreach (\Statamic\Facades\Search::indexes() as $index)
            <input type="hidden" name="indexes[]" value="{{ $index->name() }}::{{ $index->locale() }}" />
        @endforeach

        <ui-button variant="primary">{{ __('Update Indexes') }}</ui-button>
    </form>
</ui-header>

<ui-card-panel heading="{{ __('Search Indexes') }}">
    @if ($errors->has('indexes'))
        <p class="p-4"><small class="help-block text-red-500">{{ $errors->first() }}</small></p>
    @endif

    <ui-table>
        <ui-table-columns>
            <ui-table-column>{{ __('Index') }}</ui-table-column>
            <ui-table-column>{{ __('Driver') }}</ui-table-column>
            <ui-table-column>{{ __('Searchables') }}</ui-table-column>
            <ui-table-column>{{ __('Fields') }}</ui-table-column>
            <ui-table-column></ui-table-column>
        </ui-table-columns>
        <ui-table-rows>
            @foreach (\Statamic\Facades\Search::indexes() as $index)
                <ui-table-row>
                    <ui-table-cell>
                        <div class="flex items-start">
                            @cp_svg('search-drivers/' . $index->config()['driver'], '-mt-0.5 flex h-6 w-6 shrink-0 me-2', 'search-drivers/local')
                            <span class="text-gray-800 dark:text-dark-150">{{ $index->title() }}</span>
                        </div>
                    </ui-table-cell>
                    <ui-table-cell>
                        {{ ucwords($index->config()['driver']) }}
                    </ui-table-cell>
                    <ui-table-cell>
                        @if (is_string($index->config()['searchables']))
                            <div class="flex flex-wrap">
                                <ui-badge>
                                    {{ $index->config()['searchables'] }}
                                </ui-badge>
                            </div>
                        @else
                            <div class="flex flex-wrap gap-1 text-sm text-gray">
                                @foreach ($index->config()['searchables'] as $searchable)
                                    <ui-badge>
                                        {{ $searchable }}
                                    </ui-badge>
                                @endforeach
                            </div>
                        @endif
                    </ui-table-cell>
                    <ui-table-cell>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($index->config()['fields'] as $field)
                                <ui-badge>
                                    {{ $field }}
                                </ui-badge>
                            @endforeach
                        </div>
                    </ui-table-cell>
                    <ui-table-cell class="text-right rtl:text-left">
                        <form method="POST" action="{{ cp_route('utilities.search') }}">
                            @csrf
                            <input
                                type="hidden"
                                name="indexes[]"
                                value="{{ $index->name() }}::{{ $index->locale() }}"
                            />
                            <ui-button type="submit" size="sm">
                                {{ __('Update') }}
                            </ui-button>
                        </form>
                    </ui-table-cell>
                </ui-table-row>
            @endforeach
        </ui-table-rows>
    </ui-table>
</ui-card-panel>

<x-statamic::docs-callout
    topic="{{ __('Search Indexes') }}"
    url="{{ Statamic::docsUrl('search#indexes') }}"
/>

@stop
