@use(Statamic\CP\Breadcrumbs\Breadcrumbs)
@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Breadcrumbs::title($taxonomyCreateLabel))
@section('wrapper_class', 'max-w-7xl')

@section('content')
    <base-term-create-form
        :actions="{{ json_encode($actions) }}"
        taxonomy-handle="{{ $taxonomy }}"
        taxonomy-create-label="{{ $taxonomyCreateLabel }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        :published="{{ json_encode($published) }}"
        :localizations="{{ json_encode($localizations) }}"
        site="{{ $locale }}"
        :can-edit-blueprint="{{ $str::bool($user->can('configure fields')) }}"
        create-another-url="{{ cp_route('taxonomies.terms.create', [$taxonomy, $locale]) }}"
        listing-url="{{ cp_route('taxonomies.show', $taxonomy) }}"
        :preview-targets="{{ json_encode($previewTargets) }}"
    ></base-term-create-form>
@endsection
