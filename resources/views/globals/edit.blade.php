@extends('statamic::layout')
@section('title', __('Edit Global Set'))

@section('content')

    <global-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        globals-url="{{ cp_route('globals.index') }}"
        id="{{ $set->id() }}"
        initial-title="{{ $set->title() }}"
        initial-handle="{{ $set->handle() }}"
        initial-reference="{{ $reference }}"
        initial-blueprint-handle="{{ $set->blueprint()->handle() }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-localized-fields="{{ json_encode($localizedFields) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :initial-localizations="{{ json_encode($localizations) }}"
        :initial-has-origin="{{ Statamic\Support\Str::bool($hasOrigin) }}"
        :initial-origin-values="{{ json_encode($originValues) }}"
        initial-site="{{ $locale }}"
        configure-save-url="{{ cp_route('globals.update-meta', $set->id()) }}"
        :can-edit="{{ json_encode($canEdit) }}"
        :can-delete="{{ json_encode($canDelete) }}"
    ></global-publish-form>

@endsection
