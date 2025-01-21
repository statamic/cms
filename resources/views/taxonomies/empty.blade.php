@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))

@section('content')

<header class="mb-6">
    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('taxonomies.index'),
        'title' => __('Taxonomies')
    ])
    <h1 v-pre>{{ __($taxonomy->title()) }}</h1>
</header>

<div class="card p-4 content">
    <div class="flex flex-wrap">
        @can('edit', $taxonomy)
        <a href="{{ cp_route('taxonomies.edit', $taxonomy->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                @cp_svg('icons/light/hammer-wrench')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue dark:text-blue-600">{{ __('Configure Taxonomy') }} @rarr</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_configure_description') }}</p>
            </div>
        </a>
        @endcan
        @can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy, \Statamic\Facades\Site::get($site)])
        <a href="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                @cp_svg('icons/light/content-writing')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue dark:text-blue-600">{{ __('Create Term') }} @rarr</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_create_term_description') }}</p>
            </div>
        </a>
        @endcan
        @can('configure fields')
        <a href="{{ cp_route('taxonomies.blueprints.index', $taxonomy->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 dark:hover:bg-dark-550 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                @cp_svg('icons/light/blueprint')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue dark:text-blue-600">{{ __('Configure Blueprints') }} @rarr</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_blueprints_description') }}</p>
            </div>
        </a>
        @endcan
        <div class="hidden first:flex justify-center items-center p-8 w-full">
            @cp_svg($svg ?? 'empty/content')
        </div>
    </div>
</div>
@include('statamic::partials.docs-callout', [
    'topic' => __('Taxonomies'),
    'url' => Statamic::docsUrl('taxonomies')
])
@stop
