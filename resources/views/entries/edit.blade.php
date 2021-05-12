@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', $breadcrumbs->title($title))
@section('wrapper_class', 'max-w-3xl')

@section('content')

    <entry-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        collection-handle="{{ $collection }}"
        :collection-has-routes="{{ Statamic\Support\Str::bool($collectionHasRoutes) }}"
        initial-title="{{ $title }}"
        initial-reference="{{ $reference }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-localized-fields="{{ json_encode($localizedFields) }}"
        :initial-meta="{{ json_encode($meta) }}"
        initial-permalink="{{ $permalink }}"
        :initial-localizations="{{ json_encode($localizations) }}"
        :initial-has-origin="{{ $str::bool($hasOrigin) }}"
        :initial-origin-values="{{ json_encode($originValues) }}"
        :initial-origin-meta="{{ json_encode($originMeta) }}"
        initial-site="{{ $locale }}"
        :initial-is-working-copy="{{ $str::bool($hasWorkingCopy) }}"
        :initial-is-root="{{ $str::bool($isRoot) }}"
        :revisions-enabled="{{ $str::bool($revisionsEnabled) }}"
        :amp="{{ $str::bool($entry->ampable()) }}"
        :initial-read-only="{{ $str::bool($readOnly) }}"
        :preloaded-assets="{{ json_encode($preloadedAssets) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :can-edit-blueprint="{{ $str::bool($user->can('configure fields')) }}"
        :can-manage-publish-state="{{ $str::bool($canManagePublishState) }}"
        create-another-url="{{ cp_route('collections.entries.create', [$collection, $locale]) }}"
        listing-url="{{ cp_route('collections.show', $collection) }}"
    ></entry-publish-form>

@endsection
