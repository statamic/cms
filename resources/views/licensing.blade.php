@extends('statamic::layout')
@section('title', __('Licensing'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('utilities.index'),
        'title' => __('Utilities')
    ])

    @if ($requestError)

        <div class="no-results md:pt-8 max-w-2xl mx-auto">
            <div class="flex flex-wrap items-center">
                <div class="w-full md:w-1/2">
                    <h1 class="mb-4">{{ __('Licensing') }}</h1>
                    <p class="text-grey-70 leading-normal mb-4 text-lg antialiased">
                        {{ __('statamic::messages.outpost_issue_try_later') }}
                    </p>
                    <a href="{{ cp_route('utilities.licensing.refresh') }}" class="btn-primary btn-lg">{{ __('Try again') }}</a>
                </div>
                <div class="hidden md:block w-1/2 pl-6">
                    @cp_svg('empty/navigation')
                </div>
            </div>
        </div>

    @else

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Licensing') }}</h1>
        </div>

        @if ($configCached)
            <div class="text-xs border border-yellow-dark rounded p-2 bg-yellow">
                <div class="font-bold mb-1">{{ __('Configuration is cached') }}</div>
                <p>{!! __('statamic::messages.licensing_config_cached_warning') !!}</p>
           </div>
        @endif

        @if ($site->key() && $site->usesIncorrectKeyFormat())
            <div class="text-xs border border-yellow-dark rounded p-2 bg-yellow {{ $configCached ? 'mt-4' : '' }}">
                <div class="font-bold mb-1">{{ __('statamic::messages.licensing_incorrect_key_format_heading') }}</div>
                <p>{!! __('statamic::messages.licensing_incorrect_key_format_body') !!}</p>
           </div>
        @endif

        <h6 class="mt-4">Site</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span class="little-dot {{ $site->valid() ? 'bg-green' : 'bg-red' }} mr-1"></span>
                        {{ $site->key() ?? __('No license key') }}
                    </td>
                    <td class="relative">
                        {{ $site->domain()['url'] ?? '' }}
                        @if ($site->hasMultipleDomains())
                            <span class="text-2xs">({{ trans_choice('and :count more', $site->additionalDomainCount()) }})</span>
                        @endif
                    </td>
                    <td class="text-right text-red">{{ $site->invalidReason() }}</td>
                </tr>
            </table>
        </div>

        <h6 class="mt-4">Core</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                <tr>
                    <td class="w-64 font-bold">
                        <span class="little-dot {{ $statamic->valid() ? 'bg-green' : 'bg-red' }} mr-1"></span>
                        Statamic @if ($statamic->pro())<span class="text-pink">Pro</span>@else Free @endif
                    </td>
                    <td>{{ $statamic->version() }}</td>
                    <td class="text-right text-red">{{ $statamic->invalidReason() }}</td>
                </tr>
            </table>
        </div>

        <h6 class="mt-4">{{ __('Addons') }}</h6>
        @if ($addons->isEmpty())
        <p class="text-sm text-grey mt-1">{{ __('No addons installed') }}</p>
        @else
        <div class="card p-0 mt-1">
            <table class="data-table">
                @foreach ($addons as $addon)
                    <tr>
                        <td class="w-64 mr-1">
                            <span class="little-dot {{ $addon->valid() ? 'bg-green' : 'bg-red' }} mr-1"></span>
                            <span class="font-bold"><a href="{{ $addon->addon()->marketplaceUrl() }}" class="text-grey hover:text-blue">{{ $addon->name() }}</a></span>
                            @if ($addon->edition())<span class="badge uppercase font-bold text-grey-60">{{ $addon->edition() ?? '' }}</span>@endif
                        </td>
                        <td>{{ $addon->version() }}</td>
                        <td class="text-right text-red">{{ $addon->invalidReason() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        @endif

        @if (!$unlistedAddons->isEmpty())
        <h6 class="mt-4">{{ __('Unlisted Addons') }}</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                @foreach ($unlistedAddons as $addon)
                    <tr>
                        <td class="w-64 font-bold mr-1">
                            <span class="little-dot bg-green mr-1"></span>
                            {{ $addon->name() }}
                        </td>
                        <td>{{ $addon->version() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
        @endif

        <div class="mt-5 py-2 border-t flex items-center">
            <a href="{{ $site->url() }}" target="_blank" class="btn btn-primary mr-2">{{ __('Edit Site') }}</a>
            @if ($addToCartUrl) <a href="{{ $addToCartUrl }}" target="_blank" class="btn mr-2">{{ __('Buy Licenses') }}</a> @endif
            <a href="{{ cp_route('utilities.licensing.refresh') }}" class="btn">{{ __('Sync') }}</a>
            <p class="ml-2 text-2xs text-grey">{{ __('statamic::messages.licensing_sync_instructions') }}</p>
        </div>

    @endif

    @include('statamic::partials.docs-callout', [
        'topic' => __('Licensing'),
        'url' => Statamic::docsUrl('licensing')
    ])

@stop
