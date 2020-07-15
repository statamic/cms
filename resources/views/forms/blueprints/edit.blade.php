@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    <blueprint-builder
        action="{{ cp_route('forms.blueprint.update', $form->handle()) }}"
        breadcrumb-url="{{ cp_route('forms.show', $form->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
