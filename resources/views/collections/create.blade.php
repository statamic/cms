@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Collection'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
<ui-create-form
    :title="__('Create Collection')"
    :subtitle="__('messages.collection_configure_intro')"
    icon="collections"
    :route="'{{ cp_route('collections.store') }}'"
    :title-instructions="__('messages.collection_configure_title_instructions')"
    :handle-instructions="__('messages.collection_configure_handle_instructions')"
></ui-create-form>
@stop
