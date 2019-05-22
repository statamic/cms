@extends('statamic::layout')

@section('content')

    <entry-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        collection-title="{{ $collection['title'] }}"
        collection-url="{{ $collection['url'] }}"
        initial-title="{{ $entry->value('title') }}"
        initial-reference="{{ $reference }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-localized-fields="{{ json_encode($localizedFields) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-published="{{ bool_str($entry->published()) }}"
        :initial-localizations="{{ json_encode($localizations) }}"
        :initial-has-origin="{{ bool_str($hasOrigin) }}"
        :initial-origin-values="{{ empty($originValues) ? '{}' : json_encode($originValues) }}"
        :initial-origin-meta="{{ empty($originMeta) ? '{}' : json_encode($originMeta) }}"
        initial-site="{{ $locale }}"
        :initial-is-working-copy="{{ bool_str($entry->hasWorkingCopy()) }}"
        :initial-is-root="{{ bool_str($isRoot) }}"
        :revisions-enabled="{{ bool_str($entry->revisionsEnabled()) }}"
        :amp="{{ bool_str($entry->ampable()) }}"
    ></entry-publish-form>

@endsection
