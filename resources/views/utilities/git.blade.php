@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb('Git', __('Utilities')))

@section('content')

<header class="mb-6">
    @include(
        'statamic::partials.breadcrumb',
        [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities'),
        ]
    )

    <div class="flex items-center justify-between">
        <h1>Git</h1>

        <form method="POST" action="{{ cp_route('utilities.git.commit') }}">
            @csrf

            <div class="flex items-center">
                <button type="submit" class="btn-primary" {{ $statuses ? '' : 'disabled' }}>{{ __('Commit') }}</button>
            </div>
        </form>
    </div>
</header>

<div class="card p-0">
    @forelse ($statuses ?? [] as $path => $status)
        <div class="{{ $loop->first ? '' : 'border-t dark:border-dark-900' }} p-4">
            <h2>
                {{ __('Repository path') }}:
                <code class="font-normal">{{ $path }}</code>
            </h2>
            <pre
                class="mt-4 rounded bg-gray-300 p-4 font-mono text-sm text-gray-700 dark:bg-dark-800 dark:text-dark-150"
                dir="ltr"
            >
{{ $status->status }}</pre
            >
            <div class="mt-4 flex text-sm text-gray dark:text-dark-150">
                <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                    <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Affected files') }}:</span>
                    {{ $status->totalCount }}
                </div>
                @if ($status->addedCount)
                    <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                        <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Added') }}:</span>
                        {{ $status->addedCount }}
                    </div>
                @endif

                @if ($status->modifiedCount)
                    <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                        <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Modified') }}:</span>
                        {{ $status->modifiedCount }}
                    </div>
                @endif

                @if ($status->deletedCount)
                    <div class="badge-pill-sm ltr:mr-4 rtl:ml-4">
                        <span class="font-medium text-gray-800 dark:text-dark-150">{{ __('Deleted') }}:</span>
                        {{ $status->deletedCount }}
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p class="p-6 italic text-gray-600 dark:text-dark-200">{{ __('statamic::messages.git_nothing_to_commit') }}</p>
    @endforelse
</div>

@stop
