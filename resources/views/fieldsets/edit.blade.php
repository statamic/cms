@extends('statamic::layout')

@section('content')

    <fieldset-edit-form
        action="{{ cp_route('fieldsets.update', $fieldset->handle()) }}"
        :initial-fieldset="{{ json_encode([
            'title' => $fieldset->title(),
            'fields' => $fieldset->fields()->values()
        ]) }}"
    ></fieldset-edit-form>

@endsection
