@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Configure Sites'))

@section('content')

<div class="max-w-5xl mx-auto">
    <sites-edit-form
        :blueprint="{{ json_encode($blueprint) }}"
        :initial-values="{{ json_encode($values) }}"
        :meta="{{ json_encode($meta) }}"
        url="{{ cp_route('sites.update') }}"
        class="-mb-8"
    ></sites-edit-form>

    <x-statamic::docs-callout
        topic="{{ __('Multi-Site') }}"
        url="{{ Statamic::docsUrl('multi-site') }}"
    />
</div>

@stop
