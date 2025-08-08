@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Global Set'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
    <ui-create-form
        :title="__('Create Global Set')"
        :subtitle="__('messages.globals_configure_intro')"
        icon="globals"
        :route="'{{ cp_route('globals.store') }}'"
        :title-instructions="__('messages.globals_configure_title_instructions')"
        :handle-instructions="__('messages.globals_configure_handle_instructions')"
    ></ui-create-form>
@endsection
