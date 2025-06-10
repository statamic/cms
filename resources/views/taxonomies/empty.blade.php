@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))

@section('content')

<header class="mb-6">
    <h1 v-pre>{{ __($taxonomy->title()) }}</h1>
</header>

<div class="card content p-4">
    <div class="flex flex-wrap">
        @can('edit', $taxonomy)
            <a
                href="{{ cp_route('taxonomies.edit', $taxonomy->handle()) }}"
                class="group flex w-full items-start rounded-md p-4 hover:bg-gray-200 dark:hover:bg-dark-550 lg:w-1/2"
            >
                <div class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4">
                    @cp_svg('icons/light/hammer-wrench')
                </div>
                <div class="mb-4 flex-1 md:mb-0 md:ltr:mr-6 md:rtl:ml-6">
                    <h3 class="mb-2 text-blue-600 dark:text-blue-600">
                        {{ __('Configure Taxonomy') }}
                        @rarr
                    </h3>
                    <p>{{ __('statamic::messages.taxonomy_next_steps_configure_description') }}</p>
                </div>
            </a>
        @endcan

        @can('create', ['Statamic\Contracts\Taxonomies\Term', $taxonomy, \Statamic\Facades\Site::get($site)])
            <a
                href="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}"
                class="group flex w-full items-start rounded-md p-4 hover:bg-gray-200 dark:hover:bg-dark-550 lg:w-1/2"
            >
                <div class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4">
                    @cp_svg('icons/light/content-writing')
                </div>
                <div class="mb-4 flex-1 md:mb-0 md:ltr:mr-6 md:rtl:ml-6">
                    <h3 class="mb-2 text-blue-600 dark:text-blue-600">
                        {{ __('Create Term') }}
                        @rarr
                    </h3>
                    <p>{{ __('statamic::messages.taxonomy_next_steps_create_term_description') }}</p>
                </div>
            </a>
        @endcan

        @can('configure fields')
            <a
                href="{{ cp_route('taxonomies.blueprints.index', $taxonomy->handle()) }}"
                class="group flex w-full items-start rounded-md p-4 hover:bg-gray-200 dark:hover:bg-dark-550 lg:w-1/2"
            >
                <div class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4">
                    @cp_svg('icons/light/blueprint')
                </div>
                <div class="mb-4 flex-1 md:mb-0 md:ltr:mr-6 md:rtl:ml-6">
                    <h3 class="mb-2 text-blue-600 dark:text-blue-600">
                        {{ __('Configure Blueprints') }}
                        @rarr
                    </h3>
                    <p>{{ __('statamic::messages.taxonomy_next_steps_blueprints_description') }}</p>
                </div>
            </a>
        @endcan

        <div class="hidden w-full items-center justify-center p-8 first:flex">
            @cp_svg($svg ?? 'empty/content')
        </div>
    </div>
</div>
@include(
    'statamic::partials.docs-callout',
    [
        'topic' => __('Taxonomies'),
        'url' => Statamic::docsUrl('taxonomies'),
    ]
)
@stop
