@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($collection->title(), 'Collections'))

@section('content')

<header class="mb-6">
    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('collections.index'),
        'title' => __('Collections')
    ])
    <h1>{{ __($collection->title()) }}</h1>
</header>

<div class="card p-4 content">
    <div class="flex flex-wrap">
        <a href="{{ cp_route('collections.edit', $collection->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800">
                @cp_svg('icons/light/hammer-wrench')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue">{{ __('Configure Collection') }} @rarr</h3>
                <p>{{ __('statamic::messages.collection_next_steps_configure_description') }}</p>
            </div>
        </a>
        <?php $multipleBlueprints = $collection->entryBlueprints()->count() > 1 ?>
        @if ($multipleBlueprints)<div
        @else<a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site]) }}"
        @endif
            class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 rounded-md group"
        >
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800">
                @cp_svg('icons/light/content-writing')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue">{{ $collection->createLabel() }} @if (!$multipleBlueprints) @rarr @endif</h3>
                <p>{{ __('statamic::messages.collection_next_steps_create_entry_description') }}</p>
                @if ($multipleBlueprints)
                    @foreach ($collection->entryBlueprints() as $blueprint)
                        <a href="{{ cp_route('collections.entries.create', [$collection->handle(), $site, 'blueprint' => $blueprint->handle()]) }}"
                           class="text-blue text-sm rtl:ml-2 ltr:mr-2">{{ $blueprint->title() }} @rarr</a>
                    @endforeach
                @endif
            </div>
        @if ($multipleBlueprints)</div>@else</a>@endif
        <a href="{{ cp_route('collections.blueprints.index', $collection->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800">
                @cp_svg('icons/light/blueprint')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue">{{ __('Configure Blueprints') }} @rarr</h3>
                <p>{{ __('statamic::messages.collection_next_steps_blueprints_description') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('collections.scaffold', $collection->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800">
                @cp_svg('icons/light/crane')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue">{{ __('Scaffold Views') }} @rarr</h3>
                <p>{{ __('statamic::messages.collection_next_steps_scaffold_description') }}</p>
            </div>
        </a>
    </div>
</div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Collections'),
        'url' => 'collection'
    ])
@stop
