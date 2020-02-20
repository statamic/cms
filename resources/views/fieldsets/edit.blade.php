@extends('statamic::layout')
@section('title', __('Edit Fieldset'))

@section('content')

    <fieldset-edit-form
        action="{{ cp_route('fieldsets.update', $fieldset->handle()) }}"
        :initial-fieldset="{{ json_encode([
            'title' => $fieldset->title(),
            'fields' => $fieldset->fields()->all()->values()
        ]) }}"
    ></fieldset-edit-form>

@endsection
