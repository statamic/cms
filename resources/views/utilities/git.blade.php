@extends('statamic::layout')
@section('title', Statamic::crumb(__('Git'), __('Utilities')))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])

        <div class="flex items-center justify-between">
            <h1>{{ __('Git') }}</h1>
            @if($statuses)
                <form method="POST" action="{{ cp_route('utilities.git.commit') }}">
                    @csrf

                    <div class="flex items-center">
                        <button type="submit" class="btn-primary">{{ __('Commit All Content Changes') }}</button>
                    </div>
                    @if ($errors->has('email'))
                        <p class="mt-1"><small class="help-block text-red">{{ $errors->first('email') }}</small></p>
                    @endif
                </form>
            @endif
        </div>
    </header>

    <div class="card p-0">
        @forelse($statuses ?? [] as $path => $status)
            <div class="p-2 {{ $loop->first ? '' : 'border-t' }}">
                <h2 class="font-bold">{{ __('Repository path') }}: <code class="font-normal italic">{{ $path }}</code></h2>
                <pre class="mt-2 p-2 rounded text-sm font-mono bg-grey-30 text-grey-70"><ansi-colored-output text="{{ $status->status }}" /> </pre>
                <div class="mt-2 text-sm text-grey flex">
                    <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">{{ __('Affected files') }}:</span> {{ $status->totalCount }}</div>
                    @if ($status->addedCount)
                        <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">{{ __('Added') }}:</span> {{ $status->addedCount }}</div>
                    @endif
                    @if ($status->modifiedCount)
                        <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">{{ __('Modified') }}:</span> {{ $status->modifiedCount }}</div>
                    @endif
                    @if ($status->deletedCount)
                        <div class="mr-2 badge-pill-sm"><span class="text-grey-80 font-medium">{{ __('Deleted') }}:</span> {{ $status->deletedCount }}</div>
                    @endif
                </div>
            </div>
        @empty
            <p class="p-3 italic text-grey-60">{{ __('Nothing to commit, content paths clean!') }}</p>
        @endforelse
    </div>


@stop
