@extends('statamic::layout')
@section('title', Statamic\trans('Create User Group'))

@section('content')

    <user-group-publish-form
        :actions="{{ json_encode($actions) }}"
        method="post"
        publish-container="base"
        :initial-title="Statamic\trans('Create Group')"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :is-creating="true"
    ></user-group-publish-form>

@endsection
