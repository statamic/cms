@extends('statamic::layout')
@section('title', Statamic\trans('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('blueprints.index'),
        'title' => Statamic\trans('Blueprints')
    ])

    <blueprint-builder
        show-title
        action="{{ cp_route('blueprints.update', [$blueprint->namespace(), $blueprint->handle()]) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :show-hidden="false"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
