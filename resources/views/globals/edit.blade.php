@extends('statamic::layout')
@section('title', __('Edit Global Set'))

@section('content')

    <global-publish-form
        publish-container="base"
        :initial-actions="{{ json_encode($actions) }}"
        method="patch"
        globals-url="{{ cp_route('globals.index') }}"
        initial-title="{{ $variables->title() }}"
        initial-handle="{{ $variables->handle() }}"
        initial-reference="{{ $reference }}"
        initial-blueprint-handle="{{ $variables->blueprint()->handle() }}"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ empty($values) ? '{}' : json_encode($values) }}"
        :initial-localized-fields="{{ json_encode($localizedFields) }}"
        :initial-meta="{{ empty($meta) ? '{}' : json_encode($meta) }}"
        :initial-localizations="{{ json_encode($localizations) }}"
        :initial-has-origin="{{ Statamic\Support\Str::bool($hasOrigin) }}"
        :initial-is-root="{{ Statamic\Support\Str::bool($isRoot) }}"
        :initial-origin-values="{{ json_encode($originValues) }}"
        initial-site="{{ $locale }}"
        :can-configure="{{ json_encode($canConfigure) }}"
        configure-url="{{ $set->editUrl() }}"
        :can-edit="{{ json_encode($canEdit) }}"
        :can-edit-blueprint="{{ $actions['editBlueprint'] ? Statamic\Support\Str::bool($user->can('configure fields')) : 'false' }}"
    ></global-publish-form>

@endsection
