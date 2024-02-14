@extends('statamic::layout')
@section('title', Statamic\trans('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('forms.show', $form->handle()),
        'title' => Statamic\trans($form->title()),
    ])

    <blueprint-builder
        action="{{ cp_route('forms.blueprint.update', $form->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :use-tabs="false"
        :is-form-blueprint="true"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
