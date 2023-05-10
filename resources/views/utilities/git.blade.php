@extends('statamic::layout')
@section('title', Statamic::crumb('Git', __('Utilities')))

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])

        <div class="flex items-center justify-between">
            <h1>Git</h1>

            <form method="POST" action="{{ cp_route('utilities.git.commit') }}">
                @csrf

                <div class="flex items-center">
                    <button type="submit" class="btn-primary" {{ $statuses ? '': 'disabled' }}>{{ __('Commit') }}</button>
                </div>
            </form>
        </div>
    </header>

    <div class="card p-0">
        @forelse($statuses ?? [] as $path => $status)
            <div class="p-4 {{ $loop->first ? '' : 'border-t' }}">
                <h2>{{ __('Repository path') }}: <code class="font-normal">{{ $path }}</code></h2>
                <pre class="mt-4 p-4 rounded text-sm font-mono bg-gray-300 text-gray-700">{{ $status->status }}</pre>
                <div class="mt-4 text-sm text-gray flex">
                    <div class="mr-4 badge-pill-sm"><span class="text-gray-800 font-medium">{{ __('Affected files') }}:</span> {{ $status->totalCount }}</div>
                    @if ($status->addedCount)
                        <div class="mr-4 badge-pill-sm"><span class="text-gray-800 font-medium">{{ __('Added') }}:</span> {{ $status->addedCount }}</div>
                    @endif
                    @if ($status->modifiedCount)
                        <div class="mr-4 badge-pill-sm"><span class="text-gray-800 font-medium">{{ __('Modified') }}:</span> {{ $status->modifiedCount }}</div>
                    @endif
                    @if ($status->deletedCount)
                        <div class="mr-4 badge-pill-sm"><span class="text-gray-800 font-medium">{{ __('Deleted') }}:</span> {{ $status->deletedCount }}</div>
                    @endif
                </div>
            </div>
        @empty
            <p class="p-6 italic text-gray-600">{{ __('statamic::messages.git_nothing_to_commit') }}</p>
        @endforelse
    </div>


@stop
