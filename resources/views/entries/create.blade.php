@extends('statamic::layout')

@section('content')

    <base-entry-create-form
        action="{{ $actions['store'] }}"
        collection-title="{{ $collection['title'] }}"
        collection-url="{{ $collection['url'] }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
    ></base-entry-create-form>

@endsection
