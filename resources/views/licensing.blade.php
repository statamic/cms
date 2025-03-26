@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Licensing'))

@section('content')

@include(
    'statamic::partials.breadcrumb',
    [
        'url' => cp_route('utilities.index'),
        'title' => __('Utilities'),
    ]
)

@if ($requestError)
    <div class="no-results mx-auto max-w-6xl md:pt-30">
        <div class="flex flex-wrap items-center">
            <div class="w-full md:w-1/2">
                <h1 class="mb-8">{{ __('Licensing') }}</h1>
                <p class="mb-8 text-lg leading-normal text-gray-700 antialiased dark:text-dark-175">
                    @if ($usingLicenseKeyFile)
                        {{ __('statamic::messages.outpost_license_key_error') }}
                    @else
                        {{ __('statamic::messages.outpost_issue_try_later') }}
                    @endif
                </p>
                <a href="{{ cp_route('utilities.licensing.refresh') }}" class="btn-primary btn-lg">
                    {{ __('Try again') }}
                </a>
            </div>
            <div class="hidden w-1/2 md:block ltr:pl-16 rtl:pr-16">
                @cp_svg('empty/navigation')
            </div>
        </div>
    </div>
@else
    <div class="mb-6 flex">
        <h1 class="flex-1">{{ __('Licensing') }}</h1>
    </div>

    @if ($configCached)
        <div class="rounded-sm border border-yellow-dark bg-yellow p-4 text-xs dark:border-none dark:bg-dark-blue-100">
            <div class="mb-2 font-bold">{{ __('Configuration is cached') }}</div>
            <p>{!! __('statamic::messages.licensing_config_cached_warning') !!}</p>
        </div>
    @endif

    @if ($site->key() && $site->usesIncorrectKeyFormat())
        <div
            class="{{ $configCached ? 'mt-8' : '' }} rounded-sm border border-yellow-dark bg-yellow p-4 text-xs dark:border-none dark:bg-dark-blue-100"
        >
            <div class="mb-2 font-bold">{{ __('statamic::messages.licensing_incorrect_key_format_heading') }}</div>
            <p>{!! __('statamic::messages.licensing_incorrect_key_format_body') !!}</p>
        </div>
    @endif

    <h6 class="mt-8">{{ __('Site') }}</h6>
    <div class="card mt-2 p-0">
        <table class="data-table">
            <tr>
                <td class="w-64 font-bold">
                    <span
                        class="little-dot {{ $site->valid() ? 'bg-green-600' : 'bg-red-500' }} ltr:mr-2 rtl:ml-2"
                    ></span>
                    {{ $site->key() ?? __('No license key') }}
                </td>
                <td class="relative">
                    {{ $site->domain()['url'] ?? '' }}
                    @if ($site->hasMultipleDomains())
                        <span class="text-2xs">
                            ({{ trans_choice('and :count more', $site->additionalDomainCount()) }})
                        </span>
                    @endif
                </td>
                <td class="text-red-500 ltr:text-right rtl:text-left">{{ $site->invalidReason() }}</td>
            </tr>
        </table>
    </div>

    <h6 class="mt-8">{{ __('Core') }}</h6>
    <div class="card mt-2 p-0">
        <table class="data-table">
            <tr>
                <td class="w-64 font-bold">
                    <span
                        class="little-dot {{ $statamic->valid() ? 'bg-green-600' : 'bg-red-500' }} ltr:mr-2 rtl:ml-2"
                    ></span>
                    {{ __('Statamic') }}

                    @if ($statamic->pro())
                        <span class="text-pink">{{ __('Pro') }}</span>
                    @else
                        {{ __('Free') }}
                    @endif
                </td>
                <td>{{ $statamic->version() }}</td>
                <td class="text-red-500 ltr:text-right rtl:text-left">{{ $statamic->invalidReason() }}</td>
            </tr>
        </table>
    </div>

    <h6 class="mt-8">{{ __('Addons') }}</h6>

    @if ($addons->isEmpty())
        <p class="mt-2 text-sm text-gray dark:text-dark-150">{{ __('No addons installed') }}</p>
    @else
        <div class="card mt-2 p-0">
            <table class="data-table">
                @foreach ($addons as $addon)
                    <tr>
                        <td class="w-64 ltr:mr-2 rtl:ml-2">
                            <span
                                class="little-dot {{ $addon->valid() ? 'bg-green-600' : 'bg-red-500' }} ltr:mr-2 rtl:ml-2"
                            ></span>
                            <span class="font-bold">
                                <a
                                    href="{{ $addon->addon()->marketplaceUrl() }}"
                                    class="text-gray hover:text-blue dark:text-dark-175 dark:hover:text-dark-blue-100"
                                >
                                    {{ $addon->name() }}
                                </a>
                            </span>
                            @if ($addon->edition())
                                <span class="badge font-bold uppercase text-gray-600 dark:text-dark-200">
                                    {{ $addon->edition() ?? '' }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $addon->version() }}</td>
                        <td class="text-red-500 ltr:text-right rtl:text-left">{{ $addon->invalidReason() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if (! $unlistedAddons->isEmpty())
        <h6 class="mt-8">{{ __('Unlisted Addons') }}</h6>
        <div class="card mt-2 p-0">
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
        </div>
    @endif

    <div class="mt-10 flex items-center border-t py-4 dark:border-dark-950">
        <a href="{{ $site->url() }}" target="_blank" class="btn btn-primary ltr:mr-4 rtl:ml-4">
            {{ __('Edit Site') }}
        </a>
        @if ($addToCartUrl)
            <a href="{{ $addToCartUrl }}" target="_blank" class="btn ltr:mr-4 rtl:ml-4">{{ __('Buy Licenses') }}</a>
        @endif

        <a href="{{ cp_route('utilities.licensing.refresh') }}" class="btn">{{ __('Sync') }}</a>
        <p class="text-2xs text-gray dark:text-dark-175 ltr:ml-4 rtl:mr-4">
            {{ __('statamic::messages.licensing_sync_instructions') }}
        </p>
    </div>
@endif

@include(
    'statamic::partials.docs-callout',
    [
        'topic' => __('Licensing'),
        'url' => Statamic::docsUrl('licensing'),
    ]
)

@stop
