@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit Fieldset'))

@section('content')
    <fieldset-edit-form
        action="{{ cp_route('fieldsets.update', $fieldset->handle()) }}"
        :initial-fieldset="{{ json_encode($fieldsetVueObject) }}"
    ></fieldset-edit-form>
@endsection
