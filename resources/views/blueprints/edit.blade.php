@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('blueprints.index'),
        'title' => __('Blueprints'),
    ])

    <blueprint-builder
        action="{{ cp_route('blueprints.update', ['blueprint' => $blueprint->handle()]) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
