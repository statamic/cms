@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Edit Blueprint'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('users.index'),
        'title' => __('Users'),
    ])

    <blueprint-builder
        action="{{ cp_route('users.blueprint.update') }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
        :can-define-localizable="false"
    ></blueprint-builder>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
