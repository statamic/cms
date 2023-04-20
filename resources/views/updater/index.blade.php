@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')

    @if ($requestError)

        <div class="no-results md:pt-8 max-w-2xl mx-auto">
            <div class="flex flex-wrap items-center">
                <div class="w-full md:w-1/2">
                    <h1 class="mb-4">{{ __('Updates') }}</h1>
                    <p class="text-grey-70 leading-normal mb-4 text-lg antialiased">
                        {{ __('statamic::messages.outpost_issue_try_later') }}
                    </p>
                    <a href="{{ cp_route('updater') }}"
                        class="btn-primary btn-lg">{{ __('Try again') }}</a>
                </div>
                <div class="hidden md:block w-1/2 pl-6">
                    @cp_svg('empty/navigation')
                </div>
            </div>
        </div>

    @else

        <div class="flex mb-3">
            <h1 class="flex-1">{{ __('Updates') }}</h1>
        </div>

        <h6 class="mt-4">Core</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                <tr>
                    <td class="w-64"><a href="{{ route('statamic.cp.updater.product', 'statamic') }}" class="text-blue font-bold">Statamic</a></td>
                    <td>{{ $statamic->currentVersion() }}</td>
                    @if ($count = $statamic->availableUpdatesCount())
                        <td class="text-right"><span class="badge-sm bg-green btn-sm">{{ trans_choice('1 update|:count updates', $count) }}</span></td>
                    @else
                        <td class="text-right">{{ __('Up to date') }}</td>
                    @endif
                </tr>
            </table>
        </div>

        <h6 class="mt-4">{{ __('Addons') }}</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                @foreach ($addons as $addon)
                <tr>
                    <td class="w-64"><a href="{{ route('statamic.cp.updater.product', $addon -> slug()) }}"
                            class="text-blue font-bold mr-1">{{ $addon -> name() }}</a>
                    <td>{{ $addon -> version() }}</td>
                    @if ($count = $addon->changelog()->availableUpdatesCount())
                    <td class="text-right"><span
                            class="badge-sm bg-green btn-sm">{{ trans_choice('1 update|:count updates', $count) }}</span></td>
                    @else
                    <td class="text-right">{{ __('Up to date') }}</td>
                    @endif
                </tr>
                @endforeach
            </table>
        </div>

        <h6 class="mt-4">{{ __('Unlisted Addons') }}</h6>
        <div class="card p-0 mt-1">
            <table class="data-table">
                @foreach ($unlistedAddons as $addon)
                    <tr>
                        <td class="w-64">{{ $addon->name() }}</td>
                        <td>{{ $addon->version() }}</td>
                    </tr>
                @endforeach
            </table>
        </div>

        @include('statamic::partials.docs-callout', [
            'topic' => __('Updates'),
            'url' => Statamic::docsUrl('updating')
        ])

    @endif

@endsection
