@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', __('Edit Term'))
@section('wrapper_class', 'max-w-3xl')

@section('content')

    <term-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        taxonomy-handle="{{ $taxonomy }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        initial-title="{{ $title }}"
        initial-reference="{{ $reference }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-localized-fields="{{ json_encode($localizedFields) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-published="{{ $str::bool($published) }}"
        initial-permalink="{{ $permalink }}"
        :initial-localizations="{{ json_encode($localizations) }}"
        :initial-has-origin="{{ $str::bool($hasOrigin) }}"
        :initial-origin-values="{{ json_encode($originValues) }}"
        :initial-origin-meta="{{ json_encode($originMeta) }}"
        initial-site="{{ $locale }}"
        :initial-is-working-copy="{{ $str::bool($hasWorkingCopy) }}"
        :initial-is-root="{{ $str::bool($isRoot) }}"
        :revisions-enabled="{{ $str::bool($revisionsEnabled) }}"
        :initial-read-only="{{ $str::bool($readOnly) }}"
        :preloaded-assets="{{ json_encode($preloadedAssets) }}"
        :breadcrumbs="{{ $breadcrumbs->toJson() }}"
        :can-edit-blueprint="{{ $str::bool($user->can('configure fields')) }}"
        create-another-url="{{ cp_route('taxonomies.terms.create', [$taxonomy, $locale]) }}"
        listing-url="{{ cp_route('taxonomies.show', $taxonomy) }}"
        :preview-targets="{{ json_encode($previewTargets) }}"
    ></term-publish-form>

@endsection
