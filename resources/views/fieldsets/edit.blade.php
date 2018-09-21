@extends('statamic::layout')

@section('content')

    <fieldset-editor
        action="{{ cp_route('fieldsets.update', $fieldset->handle()) }}"
        :initial-fieldset="{{ json_encode([
            'title' => $fieldset->title(),
            'fields' => $fieldset->fields()->values()
        ]) }}"
    ></fieldset-editor>

@endsection
