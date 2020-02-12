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

<div class="card p-0 content">
    <div class="flex flex-wrap">
        <a href="{{ cp_route('collections.edit', $collection->handle()) }}" class="w-full lg:w-1/2 p-3 lg:border-r md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-grey-80">
                @svg('hammer-wrench')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Configure Collection') }} &rarr;</h3>
                <p>{{ __('statamic::messages.collection_next_steps_configure_description') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}" class="w-full lg:w-1/2 p-3 border-t lg:border-none md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-grey-80">
                @svg('content-writing')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Create Entry') }} &rarr;</h3>
                <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('collections.scaffold', $collection->handle()) }}" class="w-full lg:w-1/2 p-3 border-t lg:border-r md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-grey-80">
                @svg('crane')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Scaffold Resources') }} &rarr;</h3>
                <p>{{ __('statamic::messages.collection_next_steps_scaffold_description') }}</p>
            </div>
        </a>
        <a href="{{ Statamic::docsUrl('collections') }}" target="_blank" class="w-full lg:w-1/2 p-3 border-t md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-grey-80">
                @svg('book-pages')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Read the Documentation') }} &rarr;</h3>
                <p>{{ __('statamic::messages.collection_next_steps_documentation_description') }}</p>
            </div>
        </a>
    </div>
</div>

@stop


@section('xcontent')

    <div class="max-w-lg mt-2 mx-auto">
        <div class="rounded p-3 shadow bg-white">
            <header class="border-b-2 border-grey-20 mb-3 pb-2">
                <h1>Next steps for {{ $collection->title() }}</h1>
            </header>
            <div class="">
                <p class="text-grey-70">By default, collections don't have defined URLs, blueprints, or dates.
                Configure your collection to set these and other options.</p>
            </div>
        </div>
    </div>

    <create-entry-button
        url="{{ cp_route('collections.entries.create', [$collection->handle(), $site->handle()]) }}"
        :blueprints="{{ $blueprints->toJson() }}">
    </create-entry-button>

@endsection
