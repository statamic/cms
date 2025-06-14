@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Licensing'))

@section('content')

@if ($requestError)
    <div class="no-results mx-auto max-w-6xl md:pt-30">
        <div class="flex flex-wrap items-center">
            <div class="w-full md:w-1/2">
                <ui-heading level="1" size="2xl" text="{{ __('Licensing') }}"></ui-heading>
                <p class="my-4 text-lg leading-normal text-gray-700 antialiased dark:text-dark-175">
                    @if ($usingLicenseKeyFile)
                        {{ __('statamic::messages.outpost_license_key_error') }}
                    @else
                        {{ __('statamic::messages.outpost_issue_try_later') }}
                    @endif
                </p>
                <ui-button href="{{ cp_route('utilities.licensing.refresh') }}" variant="primary">
                    {{ __('Try again') }}
                </ui-button>
            </div>
            <div class="hidden w-1/2 md:block ltr:pl-16 rtl:pr-16">
                @cp_svg('empty/navigation')
            </div>
        </div>
    </div>
@else
    <ui-header title="{{ __('Licensing') }}">
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

        <ui-card-panel heading="{{ __('Site') }}">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span
                            class="little-dot {{ $site->valid() ? 'bg-green-600' : 'bg-red-700' }} me-2"
                        ></span>
                        {{ $site->key() ?? __('No license key') }}
                    </td>
                    <td class="relative">
                        {{ $site->domain()['url'] ?? '' }}
                        @if ($site->hasMultipleDomains())
                            <span class="text-xs">
                                ({{ trans_choice('and :count more', $site->additionalDomainCount()) }})
                            </span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if ($site->invalidReason())
                            <ui-badge color="red">
                                {{ $site->invalidReason() }}
                            </ui-badge>
                        @endif
                    </td>
                </tr>
            </table>
        </ui-card-panel>

        <ui-card-panel heading="{{ __('Core') }}">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span
                            class="little-dot {{ $statamic->valid() ? 'bg-green-600' : 'bg-red-700' }} ltr:mr-2 rtl:ml-2"
                        ></span>
                        {{ __('Statamic') }}

                        @if ($statamic->pro())
                            <span class="text-pink">{{ __('Pro') }}</span>
                        @else
                            {{ __('Free') }}
                        @endif
                    </td>
                    <td>{{ $statamic->version() }}</td>
                    <td class="text-end">
                        @if ($statamic->invalidReason())
                            <ui-badge color="red">
                                {{ $statamic->invalidReason() }}
                            </ui-badge>
                        @endif
                    </td>
                </tr>
            </table>
        </ui-card-panel>

        @if (! $addons->isEmpty())
            <ui-card-panel heading="{{ __('Addons') }}" class="mt-6">
                <table class="data-table">
                    @foreach ($addons as $addon)
                        <tr>
                            <td class="w-64 ltr:mr-2 rtl:ml-2">
                                <span
                                    class="little-dot {{ $addon->valid() ? 'bg-green-600' : 'bg-red-700' }} ltr:mr-2 rtl:ml-2"
                                ></span>
                                <span class="font-bold">
                                    <a
                                        href="{{ $addon->addon()->marketplaceUrl() }}"
                                        class="text-gray hover:text-blue-600 dark:text-dark-175 dark:hover:text-dark-blue-100"
                                    >
                                        {{ $addon->name() }}
                                    </a>
                                </span>
                                @if ($addon->edition())
                                    <ui-badge>
                                        {{ $addon->edition() ?? '' }}
                                    </ui-badge>
                                @endif
                            </td>
                            <td>{{ $addon->version() }}</td>
                            <td class="text-red-700 ltr:text-right rtl:text-left">{{ $addon->invalidReason() }}</td>
                        </tr>
                    @endforeach
                </table>
            </ui-card-panel>
        @endif

        @if (! $unlistedAddons->isEmpty())
            <ui-card-panel heading="{{ __('Unlisted Addons') }}">
                <table class="data-table">
                    @foreach ($unlistedAddons as $addon)
                        <tr>
                            <td class="w-64 font-bold ltr:mr-2 rtl:ml-2">
                                <span class="little-dot bg-green-600 ltr:mr-2 rtl:ml-2"></span>
                                {{ $addon->name() }}
                            </td>
                            <td>{{ $addon->version() }}</td>
                        </tr>
                    @endforeach
                </table>
            </ui-card-panel>
        @endif
    </section>
@endif

    <x-statamic::docs-callout
        topic="{{ __('Licensing') }}"
        url="{{ Statamic::docsUrl('licensing') }}"
    />

@stop
