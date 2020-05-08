@extends('statamic::layout')
@section('title', __('Updater'))

@section('content')

    @include('statamic::updater.partials.header')

    <h6 class="mt-4">Core</h6>
    <div class="card p-0 mt-1">
        <table class="data-table">
            <tr>
                <td class="w-64"><a href="{{ route('statamic.cp.updater.product', 'statamic') }}" class="text-blue font-bold">Statamic</a></td>
                <td>{{ $statamic->currentVersion() }}</td>
                @if ($count = $statamic->availableUpdatesCount())
                    <td class="text-right"><span class="badge-sm bg-green btn-sm">{{ $count }} Updates</span></td>
                @else
                    <td class="text-right">Up to date</td>
                @endif
            </tr>
        </table>
    </div>

    <h6 class="mt-4">Addons</h6>
    <div class="card p-0 mt-1">
        <table class="data-table">
            @foreach ($addons as $addon)
                <tr>
                    <td class="w-64"><a href="{{ route('statamic.cp.updater.product', $addon->slug()) }}" class="text-blue font-bold mr-1">{{ $addon->name() }}</a>
                    <td>{{ $addon->version() }}</td>
                    @if ($count = $addon->changelog()->availableUpdatesCount())
                        <td class="text-right"><span class="badge-sm bg-green btn-sm">{{ $count }} Updates</span></td>
                    @else
                        <td class="text-right">Up to date</td>
                    @endif
                </tr>
            @endforeach
        </table>
    </div>

@endsection
