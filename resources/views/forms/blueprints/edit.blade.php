@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')
    <blueprint-builder
        action="{{ cp_route('forms.blueprint.update', $form->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :use-tabs="false"
        :is-form-blueprint="true"
        :can-define-localizable="false"
    ></blueprint-builder>

    <x-statamic::docs-callout
        :topic="__('Blueprints')"
        :url="Statamic::docsUrl('blueprints')"
    />
@endsection
