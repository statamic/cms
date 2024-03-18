@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))

@section('content')

<header class="mb-6">
    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('taxonomies.index'),
        'title' => __('Taxonomies')
    ])
    <h1>{{ __($taxonomy->title()) }}</h1>
</header>

<div class="card p-4 content">
    <div class="flex flex-wrap">
        <a href="{{ cp_route('taxonomies.edit', $taxonomy->handle()) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800">
                @cp_svg('icons/light/hammer-wrench')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue">{{ __('Configure Taxonomy') }} @rarr</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_configure_description') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800">
                @cp_svg('icons/light/content-writing')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue">{{ __('Create Term') }} @rarr</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_create_term_description') }}</p>
            </div>
        </a>
        <a href="{{ Statamic::docsUrl('taxonomies') }}" target="_blank" class="w-full lg:w-1/2 p-4 flex items-start hover:bg-gray-200 rounded-md group">
            <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800">
                @cp_svg('icons/light/book-pages')
            </div>
            <div class="flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                <h3 class="mb-2 text-blue">{{ __('Read the Documentation') }} @rarr</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_documentation_description') }}</p>
            </div>
        </a>
    </div>
</div>
@stop
