@extends('statamic::layout')
@section('title', $breadcrumbs->title('Create Term'))
@section('wrapper_class', 'max-w-3xl')

@section('content')
    <base-term-create-form
        :actions="{{ json_encode($actions) }}"
        taxonomy-handle="{{ $taxonomy }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :published="{{ json_encode($published) }}"
        :localizations="{{ json_encode($localizations) }}"
    ></base-term-create-form>

@endsection
