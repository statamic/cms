@use(Statamic\CP\Breadcrumbs\Breadcrumbs)
@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Breadcrumbs::title($collectionCreateLabel))
@section('wrapper_class', 'max-w-7xl')

@section('content')
    <base-entry-create-form
        :actions="{{ json_encode($actions) }}"
        collection-handle="{{ $collection }}"
        collection-create-label="{{ $collectionCreateLabel }}"
        :collection-has-routes="{{ Statamic\Support\Str::bool($collectionHasRoutes) }}"
        :fieldset="{{ json_encode($blueprint) }}"
        :values="{{ json_encode($values) }}"
        :extra-values="{{ json_encode($extraValues) }}"
        :meta="{{ json_encode($meta) }}"
        :published="{{ json_encode($published) }}"
        :localizations="{{ json_encode($localizations) }}"
        :revisions="{{ Statamic\Support\Str::bool($revisionsEnabled) }}"
        site="{{ $locale }}"
        parent="{{ $parent }}"
        create-another-url="{{ cp_route('collections.entries.create', [$collection, $locale, 'blueprint' => $blueprint['handle'], 'parent' => $values['parent'] ?? null]) }}"
        listing-url="{{ cp_route('collections.show', $collection) }}"
        :can-edit-blueprint="{{ $str::bool($user->can('configure fields')) }}"
        :can-manage-publish-state="{{ Statamic\Support\Str::bool($canManagePublishState) }}"
        :preview-targets="{{ json_encode($previewTargets) }}"
    ></base-entry-create-form>
@endsection
