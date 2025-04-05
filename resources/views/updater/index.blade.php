@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Updater'))

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
        <div class="mb-6 flex">
            <h1 class="flex-1">{{ __('Updates') }}</h1>
        </div>

        <h6 class="mt-8">{{ __('Core') }}</h6>
        <div class="card mt-2 p-0">
            <table class="data-table">
                <tr>
                    <td class="w-64">
                        <a href="{{ route('statamic.cp.updater.product', 'statamic') }}" class="font-bold text-blue">
                            {{ __('Statamic') }}
                        </a>
                    </td>
                    <td>{{ $statamic->currentVersion() }}</td>

                    @if ($count = $statamic->availableUpdatesCount())
                        <td class="ltr:text-right rtl:text-left">
                            <span class="badge-sm btn-xs bg-green-600">
                                {{ trans_choice('1 update|:count updates', $count) }}
                            </span>
                        </td>
                    @else
                        <td class="ltr:text-right rtl:text-left">{{ __('Up to date') }}</td>
                    @endif
                </tr>
            </table>
        </div>

        @if ($addons->count())
            <h6 class="mt-8">{{ __('Addons') }}</h6>
            <div class="card mt-2 p-0">
                <table class="data-table">
                    @foreach ($addons as $addon)
                        <tr>
                            <td class="w-64">
                                <a
                                    href="{{ route('statamic.cp.updater.product', $addon->slug()) }}"
                                    class="font-bold text-blue ltr:mr-2 rtl:ml-2"
                                >
                                    {{ $addon->name() }}
                                </a>
                            </td>

                            <td>{{ $addon->version() }}</td>

                            @if ($count = $addon->changelog()->availableUpdatesCount())
                                <td class="ltr:text-right rtl:text-left">
                                    <span class="badge-sm btn-xs bg-green-600">
                                        {{ trans_choice('1 update|:count updates', $count) }}
                                    </span>
                                </td>
                            @else
                                <td class="ltr:text-right rtl:text-left">{{ __('Up to date') }}</td>
                            @endif
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @if ($unlistedAddons->count())
            <h6 class="mt-8">{{ __('Unlisted Addons') }}</h6>
            <div class="card mt-2 p-0">
                <table class="data-table">
                    @foreach ($unlistedAddons as $addon)
                        <tr>
                            <td class="w-64">{{ $addon->name() }}</td>
                            <td>{{ $addon->version() }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif

        @include(
            'statamic::partials.docs-callout',
            [
                'topic' => __('Updates'),
                'url' => Statamic::docsUrl('updating'),
            ]
        )
    @endif
@endsection
