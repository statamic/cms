@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))

@section('content')

<header class="mb-6">
    <h1 v-pre>{{ __($collection->title()) }}</h1>
</header>

<div class="card p-4 content">
    <div class="flex flex-wrap">
        @can('edit', $collection)
        <a href="{{ cp_route('collections.edit', $collection->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                @cp_svg('icons/light/hammer-wrench')
            </div>
            <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __('Configure Collection') }} @rarr</h3>
                <p>{{ __('statamic::messages.collection_next_steps_configure_description') }}</p>
            </div>
        </a>
        @endcan
        @can('create', ['Statamic\Contracts\Entries\Entry', $collection, \Statamic\Facades\Site::get($site)])
        <?php $multipleBlueprints = $collection->entryBlueprints()->count() > 1 ?>
        @if ($multipleBlueprints)<div
        @else<a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site]) }}"
        @endif
            class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group"
        >
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                @cp_svg('icons/light/content-writing')
            </div>
            <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ $collection->createLabel() }} @if (!$multipleBlueprints) @rarr @endif</h3>
                <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                @if ($multipleBlueprints)
                    @foreach ($collection->entryBlueprints() as $blueprint)
                        <a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site, 'blueprint' => $blueprint->handle()]) }}"
                           class="text-blue-600 text-sm rtl:ml-2 ltr:mr-2">{{ $blueprint->title() }} @rarr</a>
                    @endforeach
                @endif
            </div>
        @if ($multipleBlueprints)</div>@else</a>@endif
        @endcan
        @can('configure fields')
        <a href="{{ cp_route('collections.blueprints.index', $collection->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                @cp_svg('icons/light/blueprint')
            </div>
            <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __('Configure Blueprints') }} @rarr</h3>
                <p>{{ __('statamic::messages.collection_next_steps_blueprints_description') }}</p>
            </div>
        </a>
        @endcan
        @can('store', 'Statamic\Contracts\Entries\Collection')
        <a href="{{ cp_route('collections.scaffold', $collection->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                @cp_svg('icons/light/crane')
            </div>
            <div class="flex-1 mb-4 md:mb-0 md:rtl:ml-6 md:ltr:mr-6">
                <h3 class="mb-2 text-blue-600 dark:text-blue-600">{{ __('Scaffold Views') }} @rarr</h3>
                <p>{{ __('statamic::messages.collection_next_steps_scaffold_description') }}</p>
            </div>
        </a>
        @endcan
        <div class="hidden first:flex justify-center items-center p-8 w-full">
            @cp_svg($svg ?? 'empty/content')
        </div>
    </div>
</div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Collections'),
        'url' => Statamic::docsUrl('collections')
    ])
@stop
