@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Licensing'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('utilities.index'),
        'title' => __('Utilities')
    ])

    @if ($requestError)

        <div class="no-results md:pt-30 max-w-2xl mx-auto">
            <div class="flex flex-wrap items-center">
                <div class="w-full md:w-1/2">
                    <h1 class="mb-8">{{ __('Licensing') }}</h1>
                    <p class="text-gray-700 dark:text-dark-175 leading-normal mb-8 text-lg antialiased">
                        @if ($usingLicenseKeyFile)
                            {{ __('statamic::messages.outpost_license_key_error') }}
                        @else
                            {{ __('statamic::messages.outpost_issue_try_later') }}
                        @endif
                    </p>
                    <a href="{{ cp_route('utilities.licensing.refresh') }}" class="btn-primary btn-lg">{{ __('Try again') }}</a>
                </div>
                <div class="hidden md:block w-1/2 rtl:pr-16 ltr:pl-16">
                    @cp_svg('empty/navigation')
                </div>
            </div>
        </div>

    @else

        <div class="flex mb-6">
            <h1 class="flex-1">{{ __('Licensing') }}</h1>
        </div>

        @if ($configCached)
            <div class="text-xs border border-yellow-dark rounded p-4 bg-yellow dark:bg-dark-blue-100 dark:border-none">
                <div class="font-bold mb-2">{{ __('Configuration is cached') }}</div>
                <p>{!! __('statamic::messages.licensing_config_cached_warning') !!}</p>
           </div>
        @endif

        @if ($site->key() && $site->usesIncorrectKeyFormat())
            <div class="text-xs border border-yellow-dark rounded p-4 bg-yellow dark:bg-dark-blue-100 dark:border-none {{ $configCached ? 'mt-8' : '' }}">
                <div class="font-bold mb-2">{{ __('statamic::messages.licensing_incorrect_key_format_heading') }}</div>
                <p>{!! __('statamic::messages.licensing_incorrect_key_format_body') !!}</p>
           </div>
        @endif

        <h6 class="mt-8">{{ __('Site') }}</h6>
        <div class="card p-0 mt-2">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span class="little-dot {{ $site->valid() ? 'bg-green-600' : 'bg-red-500' }} rtl:ml-2 ltr:mr-2"></span>
                        {{ $site->key() ?? __('No license key') }}
                    </td>
                    <td class="relative">
                        {{ $site->domain()['url'] ?? '' }}
                        @if ($site->hasMultipleDomains())
                            <span class="text-2xs">({{ trans_choice('and :count more', $site->additionalDomainCount()) }})</span>
                        @endif
                    </td>
                    <td class="rtl:text-left ltr:text-right text-red-500">{{ $site->invalidReason() }}</td>
                </tr>
            </table>
        </div>

        <h6 class="mt-8">{{ __('Core') }}</h6>
        <div class="card p-0 mt-2">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span class="little-dot {{ $statamic->valid() ? 'bg-green-600' : 'bg-red-500' }} rtl:ml-2 ltr:mr-2"></span>
                        {{ __('Statamic') }} @if ($statamic->pro())<span class="text-pink">{{ __('Pro') }}</span>@else {{ __('Free') }} @endif
                    </td>
                    <td>{{ $statamic->version() }}</td>
                    <td class="rtl:text-left ltr:text-right text-red-500">{{ $statamic->invalidReason() }}</td>
                </tr>
            </table>
        </div>

        <h6 class="mt-8">{{ __('Addons') }}</h6>
        @if ($addons->isEmpty())
        <p class="text-sm text-gray dark:text-dark-150 mt-2">{{ __('No addons installed') }}</p>
        @else
        <div class="card p-0 mt-2">
            <table class="data-table">
                @foreach ($addons as $addon)
                    <tr>
                        <td class="w-64 rtl:ml-2 ltr:mr-2">
                            <span class="little-dot {{ $addon->valid() ? 'bg-green-600' : 'bg-red-500' }} rtl:ml-2 ltr:mr-2"></span>
                            <span class="font-bold"><a href="{{ $addon->addon()->marketplaceUrl() }}" class="text-gray dark:text-dark-175 hover:text-blue dark:hover:text-dark-blue-100">{{ $addon->name() }}</a></span>
                            @if ($addon->edition())<span class="badge uppercase font-bold text-gray-600 dark:text-dark-200">{{ $addon->edition() ?? '' }}</span>@endif
                        </td>
                        <td>{{ $addon->version() }}</td>
                        <td class="rtl:text-left ltr:text-right text-red-500">{{ $addon->invalidReason() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        @endif

        @if (!$unlistedAddons->isEmpty())
        <h6 class="mt-8">{{ __('Unlisted Addons') }}</h6>
        <div class="card p-0 mt-2">
            <table class="data-table">
                @foreach ($unlistedAddons as $addon)
                    <tr>
                        <td class="w-64 font-bold rtl:ml-2 ltr:mr-2">
                            <span class="little-dot bg-green-600 rtl:ml-2 ltr:mr-2"></span>
                            {{ $addon->name() }}
                        </td>
                        <td>{{ $addon->version() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        @endif

        <div class="mt-10 py-4 border-t dark:border-dark-950 flex items-center">
            <a href="{{ $site->url() }}" target="_blank" class="btn btn-primary rtl:ml-4 ltr:mr-4">{{ __('Edit Site') }}</a>
            @if ($addToCartUrl) <a href="{{ $addToCartUrl }}" target="_blank" class="btn rtl:ml-4 ltr:mr-4">{{ __('Buy Licenses') }}</a> @endif
            <a href="{{ cp_route('utilities.licensing.refresh') }}" class="btn">{{ __('Sync') }}</a>
            <p class="rtl:mr-4 ltr:ml-4 text-2xs text-gray dark:text-dark-175">{{ __('statamic::messages.licensing_sync_instructions') }}</p>
        </div>

    @endif

    @include('statamic::partials.docs-callout', [
        'topic' => __('Licensing'),
        'url' => Statamic::docsUrl('licensing')
    ])

@stop
