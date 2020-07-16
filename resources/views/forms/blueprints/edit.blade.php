@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('forms.show', $form->handle()),
        'title' => $form->title(),
    ])

    <blueprint-builder
        action="{{ cp_route('forms.blueprint.update', $form->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
