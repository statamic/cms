@extends('statamic::layout')
@section('title', $breadcrumbs->title('Create Entry'))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <base-entry-create-form
        :actions="{{ json_encode($actions) }}"
        collection-handle="{{ $collection }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :published="{{ json_encode($published) }}"
        :localizations="{{ json_encode($localizations) }}"
        :revisions="{{ Statamic\Support\Str::bool($revisionsEnabled ) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        site="{{ $locale }}"
        create-another-url="{{ cp_route('collections.entries.create', [$collection, $locale]) }}"
        listing-url="{{ cp_route('collections.show', $collection) }}"
    ></base-entry-create-form>

@endsection
