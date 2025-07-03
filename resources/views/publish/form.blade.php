@extends('statamic::layout')

@section('content')
    <ui-publish-form
        @if ($icon)icon="{{ $icon }}"@endif
        title="{{ $title }}"
        :blueprint="{{ Js::from($blueprint) }}"
        :initial-values="{{ Js::from($values) }}"
        :initial-meta="{{ Js::from($meta) }}"
        submit-url="{{ $submitUrl }}"
        submit-method="{{ $submitMethod }}"
        :read-only="{{ Js::from($readOnly) }}"
    ></ui-publish-form>
@endsection
