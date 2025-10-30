@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create User Group'))

@section('content')
    <user-group-publish-form
        :actions="{{ json_encode($actions) }}"
        method="post"
        publish-container="base"
        :initial-title="__('Create Group')"
        :initial-fieldset="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :initial-meta="{{ json_encode($meta) }}"
        :is-creating="true"
    ></user-group-publish-form>
@endsection
