@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Taxonomy'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <ui-create-form
        :title="__('Create Taxonomy')"
        :subtitle="__('messages.taxonomy_configure_intro')"
        icon="taxonomies"
        :route="'{{ cp_route('taxonomies.store') }}'"
        :title-instructions="__('messages.taxonomy_configure_title_instructions')"
        :handle-instructions="__('messages.taxonomy_configure_handle_instructions')"
    ></ui-create-form>
@endsection
