@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Updates'))

@section('content')
    @if ($requestError)
        <div class="no-results mx-auto max-w-6xl md:pt-30">
            <div class="flex flex-wrap items-center">
                <div class="w-full md:w-1/2">
                    <h1 class="mb-8">{{ __('Updates') }}</h1>
                    <p class="mb-8 text-lg leading-normal text-gray-700 antialiased">
                        {{ __('statamic::messages.outpost_issue_try_later') }}
                    </p>
                    <a href="{{ cp_route('updater') }}" class="btn-primary btn-lg">{{ __('Try again') }}</a>
                </div>
                <div class="hidden w-1/2 md:block ltr:pl-16 rtl:pr-16">
                    @cp_svg('empty/navigation')
                </div>
            </div>
        </div>
    @else
        <ui-header title="{{ __('Updates') }}" icon="updates"></ui-header>

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

            @if ($unlistedAddons->count())
                <ui-panel heading="{{ __('Unlisted Addons') }}">
                    <ui-card class="py-0!">
                        <ui-table class="w-full">
                            @foreach ($unlistedAddons as $addon)
                                <ui-table-row>
                                    <ui-table-cell class="w-64 font-bold">{{ $addon->name() }}</ui-table-cell>
                                    <ui-table-cell>{{ $addon->version() }}</ui-table-cell>
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
