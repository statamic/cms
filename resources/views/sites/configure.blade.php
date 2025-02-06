@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Configure Sites'))

@section('content')

<sites-edit-form
    :blueprint="{{ json_encode($blueprint) }}"
    :initial-values="{{ json_encode($values) }}"
    :meta="{{ json_encode($meta) }}"
    url="{{ cp_route('sites.update') }}"
    class="-mb-8"
></sites-edit-form>

@include(
    'statamic::partials.docs-callout',
    [
        'topic' => __('Multi-Site'),
        'url' => Statamic::docsUrl('multi-site'),
    ]
)

@stop
