@extends('statamic::layout')
@section('title', Statamic\trans('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('user-groups.index'),
        'title' => Statamic\trans('User Groups'),
    ])

    <blueprint-builder
        action="{{ cp_route('user-groups.blueprint.update') }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
