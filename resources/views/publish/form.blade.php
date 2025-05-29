@extends('statamic::layout')

@section('content')
    <ui-publish-form
        title="Generic Publish Form"
        :blueprint="{{ Js::from($blueprint) }}"
        :initial-values="{{ Js::from($values) }}"
        :initial-meta="{{ Js::from($meta) }}"
        submit-url="{{ $submitUrl }}"
        submit-method="{{ $submitMethod }}"
    ></ui-publish-form>
@endsection
