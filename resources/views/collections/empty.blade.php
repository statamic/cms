@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))

@section('content')

<header class="mb-3">
    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('collections.index'),
        'title' => __('Collections')
    ])
    <h1>{{ $collection->title() }}</h1>
</header>

<div class="card p-2 content">
    <div class="flex flex-wrap">
        <a href="{{ cp_route('collections.edit', $collection->handle()) }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('hammer-wrench')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue">{{ __('Configure Collection') }} &rarr;</h3>
                <p>{{ __('statamic::messages.collection_next_steps_configure_description') }}</p>
            </div>
        </a>
        <?php $multipleBlueprints = $collection->entryBlueprints()->count() > 1 ?>
        @if ($multipleBlueprints)<div
        @else<a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site]) }}"
        @endif
            class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group"
        >
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('content-writing')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue">{{ __('Create Entry') }} @if (!$multipleBlueprints)&rarr;@endif</h3>
                <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                @if ($multipleBlueprints)
                    @foreach ($collection->entryBlueprints() as $blueprint)
                        <a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site, 'blueprint' => $blueprint->handle()]) }}"
                           class="text-blue text-sm mr-1">{{ $blueprint->title() }} &rarr;</a>
                    @endforeach
                @endif
            </div>
        @if ($multipleBlueprints)</div>@else</a>@endif
        <a href="{{ cp_route('collections.scaffold', $collection->handle()) }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('crane')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue">{{ __('Scaffold Resources') }} &rarr;</h3>
                <p>{{ __('statamic::messages.collection_next_steps_scaffold_description') }}</p>
            </div>
        </a>
        <a href="{{ Statamic::docsUrl('collections') }}" target="_blank" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('book-pages')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue">{{ __('Read the Documentation') }} &rarr;</h3>
                <p>{{ __('statamic::messages.collection_next_steps_documentation_description') }}</p>
            </div>
        </a>
    </div>
</div>
@stop
