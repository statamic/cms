@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Licensing'))

@section('content')
    <ui-header title="{{ __('Licensing') }}" icon="license">
        <ui-button
            href="{{ $site->url() }}"
            target="_blank"
            text="{{ __('Manage on statamic.com') }}"
        ></ui-button>
        @if ($addToCartUrl)
            <ui-button
                href="{{ $addToCartUrl }}"
                target="_blank"
                text="{{ __('Buy Licenses') }}"
            ></ui-button>
        @endif
        <ui-tooltip side="bottom" text="{{ __('statamic::messages.licensing_sync_instructions') }}">
            <ui-button
                href="{{ cp_route('utilities.licensing.refresh') }}"
                variant="primary"
                text="{{ __('Sync') }}"
            ></ui-button>
        </ui-tooltip>
    </ui-header>

    @if ($requestError)
        <ui-card class="w-full space-y-4 flex items-center justify-between">
            <ui-heading size="lg" class="mb-0!" text="{{ $usingLicenseKeyFile ? __('statamic::messages.outpost_license_key_error') : __('statamic::messages.outpost_issue_try_later') }}" icon="warning-diamond"></ui-heading>
            <ui-button href="{{ cp_route('utilities.licensing.refresh') }}" variant="primary">
                {{ __('Try Again') }}
            </ui-button>
        </ui-card>
    @else

        <section class="space-y-6">
            @if ($configCached)
                <ui-card-panel heading="{{ __('Configuration is cached') }}">
                    <p class="text-gray-700 text-sm">{!! __('statamic::messages.licensing_config_cached_warning') !!}</p>
                </ui-card-panel>
            @endif

            @if ($site->key() && $site->usesIncorrectKeyFormat())
                <ui-card-panel heading="{{ __('statamic::messages.licensing_incorrect_key_format_heading') }}">
                    <p class="text-gray-700 text-sm">{!! __('statamic::messages.licensing_incorrect_key_format_body') !!}</p>
                </ui-card-panel>
            @endif

            <ui-panel heading="{{ __('Site') }}">
                <ui-card class="py-0!">
                    <ui-table class="w-full">
                        <ui-table-row>
                            <ui-table-cell class="w-64 font-bold">
                                <span class="little-dot {{ $site->valid() ? 'bg-green-600' : 'bg-red-700' }} me-2"></span>
                                {{ $site->key() ?? __('No license key') }}
                            </ui-table-cell>
                            <ui-table-cell class="relative">
                                {{ $site->domain()['url'] ?? '' }}
                                @if ($site->hasMultipleDomains())
                                    <span class="text-xs">
                                        ({{ trans_choice('and :count more', $site->additionalDomainCount()) }})
                                    </span>
                                @endif
                            </ui-table-cell>
                            <ui-table-cell class="text-end">
                                @if ($site->invalidReason())
                                    <ui-badge color="red">
                                        {{ $site->invalidReason() }}
                                    </ui-badge>
                                @endif
                            </ui-table-cell>
                        </ui-table-row>
                    </ui-table>
                </ui-card>
            </ui-panel>

            <ui-panel heading="{{ __('Core') }}">
                <ui-card class="py-0!">
                    <ui-table class="w-full">
                        <ui-table-row>
                            <ui-table-cell class="w-64 font-bold">
                                <span class="little-dot {{ $statamic->valid() ? 'bg-green-600' : 'bg-red-700' }} me-2"></span>
                                {{ __('Statamic') }}

                                @if ($statamic->pro())
                                    <span class="text-pink">{{ __('Pro') }}</span>
                                @else
                                    {{ __('Free') }}
                                @endif
                            </ui-table-cell>
                            <ui-table-cell>{{ $statamic->version() }}</ui-table-cell>
                            <ui-table-cell class="text-end">
                                @if ($statamic->invalidReason())
                                    <ui-badge color="red">
                                        {{ $statamic->invalidReason() }}
                                    </ui-badge>
                                @endif
                            </ui-table-cell>
                        </ui-table-row>
                    </ui-table>
                </ui-card>
            </ui-panel>

            @if (! $addons->isEmpty())
                <ui-panel heading="{{ __('Addons') }}">
                    <ui-card class="py-0!">
                        <ui-table class="w-full">
                            @foreach ($addons as $addon)
                                <ui-table-row>
                                    <ui-table-cell class="w-64 me-2">
                                        <span class="little-dot {{ $addon->valid() ? 'bg-green-600' : 'bg-red-700' }} me-2"></span>
                                        <span class="font-bold">
                                            <a href="{{ $addon->addon()->marketplaceUrl() }}" class="underline">
                                                {{ $addon->name() }}
                                            </a>
                                        </span>
                                        @if ($addon->edition())
                                            <ui-badge>
                                                {{ $addon->edition() ?? '' }}
                                            </ui-badge>
                                        @endif
                                    </ui-table-cell>
                                    <ui-table-cell>{{ $addon->version() }}</ui-table-cell>
                                    <ui-table-cell class="text-red-700 text-end">{{ $addon->invalidReason() }}</ui-table-cell>
                                </ui-table-row>
                            @endforeach
                        </ui-table>
                    </ui-card>
                </ui-panel>
            @endif

            @if (! $unlistedAddons->isEmpty())
                <ui-panel heading="{{ __('Unlisted Addons') }}">
                    <ui-card class="py-0!">
                        <ui-table class="w-full">
                            @foreach ($unlistedAddons as $addon)
                                <ui-table-row>
                                    <ui-table-cell class="w-64 font-bold me-2">
                                        <span class="little-dot bg-green-600 me-2"></span>
                                        {{ $addon->name() }}
                                    </ui-table-cell>
                                    <ui-table-cell>{{ $addon->version() }}</ui-table-cell>
                                </ui-table-row>
                            @endforeach
                        </ui-table>
                    </ui-card>
                </ui-panel>
            @endif
        </section>
    @endif

    <x-statamic::docs-callout
        topic="{{ __('Licensing') }}"
        url="{{ Statamic::docsUrl('licensing') }}"
    />

@stop
