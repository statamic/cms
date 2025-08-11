@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Updates'))

@section('content')
    <ui-header title="{{ __('Updates') }}" icon="updates"></ui-header>

    @if ($requestError)
        <ui-card class="w-full space-y-4 flex items-center justify-between">
            <ui-heading size="lg" class="mb-0!" text="{{ __('statamic::messages.outpost_issue_try_later') }}" icon="warning-diamond"></ui-heading>
            <ui-button href="{{ cp_route('updater') }}" variant="primary">
                {{ __('Try Again') }}
            </ui-button>
        </ui-card>
    @else
        <section class="space-y-6">
            <ui-panel heading="{{ __('Core') }}">
                <ui-card class="py-0!">
                    <ui-table class="w-full">
                        <ui-table-row>
                            <ui-table-cell class="w-64 font-bold">
                                <a href="{{ route('statamic.cp.updater.product', 'statamic') }}">
                                    {{ __('Statamic') }}
                                </a>
                            </ui-table-cell>
                            <ui-table-cell>{{ $statamic->currentVersion() }}</ui-table-cell>
                            @if ($count = $statamic->availableUpdatesCount())
                                <ui-table-cell class="text-right">
                                    <ui-badge size="sm" color="green">{{ trans_choice('1 update|:count updates', $count) }}</ui-badge>
                                </ui-table-cell>
                            @else
                                <ui-table-cell class="text-right">{{ __('Up to date') }}</ui-table-cell>
                            @endif
                        </ui-table-row>
                    </ui-table>
                </ui-card>
            </ui-panel>

            @if ($addons->count())
                <ui-panel heading="{{ __('Addons') }}">
                    <ui-card class="py-0!">
                        <ui-table class="w-full">
                            @foreach ($addons as $addon)
                                <ui-table-row>
                                    <ui-table-cell class="w-64 font-bold">
                                        <a href="{{ route('statamic.cp.updater.product', $addon->slug()) }}">
                                            {{ $addon->name() }}
                                        </a>
                                    </ui-table-cell>
                                    <ui-table-cell>{{ $addon->version() }}</ui-table-cell>
                                    @if ($count = $addon->changelog()->availableUpdatesCount())
                                        <ui-table-cell class="text-right">
                                            <ui-badge size="sm" color="green">{{ trans_choice('1 update|:count updates', $count) }}</ui-badge>
                                        </ui-table-cell>
                                    @else
                                        <ui-table-cell class="text-right">{{ __('Up to date') }}</ui-table-cell>
                                    @endif
                                </ui-table-row>
                            @endforeach
                        </ui-table>
                    </ui-card>
                </ui-panel>
            @endif
        </section>

        <x-statamic::docs-callout
            :topic="__('Updates')"
            :url="Statamic::docsUrl('updating')"
        />
    @endif
@endsection
