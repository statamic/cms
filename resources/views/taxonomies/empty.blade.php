@extends('statamic::layout')
@section('title', Statamic::crumb($taxonomy->title(), 'Taxonomies'))

@section('content')

<header class="mb-3">
    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('taxonomies.index'),
        'title' => __('Taxonomies')
    ])
    <h1>{{ $taxonomy->title() }}</h1>
</header>

<div class="card p-2 content">
    <div class="flex flex-wrap">
        <a href="{{ cp_route('taxonomies.edit', $taxonomy->handle()) }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('hammer-wrench')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue">{{ __('Configure Taxonomy') }} &rarr;</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_configure_description') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('taxonomies.terms.create', [$taxonomy->handle(), $site]) }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('content-writing')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue">{{ __('Create Term') }} &rarr;</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_create_term_description') }}</p>
            </div>
        </a>
        <a href="{{ Statamic::docsUrl('taxonomies') }}" target="_blank" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('book-pages')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue">{{ __('Read the Documentation') }} &rarr;</h3>
                <p>{{ __('statamic::messages.taxonomy_next_steps_documentation_description') }}</p>
            </div>
        </a>
    </div>
</div>
@stop
