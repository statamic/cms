@extends('statamic::layout')

@section('content')

    <entry-publish-form
        publish-container="base"
        action="{{ $actions['update'] }}"
        method="patch"
        collection-title="{{ $collection['title'] }}"
        collection-url="{{ $collection['url'] }}"
        initial-title="{{ $entry->get('title') }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
    ></entry-publish-form>

@endsection





