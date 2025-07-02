@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Navigation'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
<ui-create-form
    :title="__('Create Navigation')"
    :subtitle="__('messages.navigation_configure_intro')"
    icon="navigation"
    :route="'{{ cp_route('navigation.store') }}'"
    :title-instructions="__('messages.navigation_configure_title_instructions')"
    :handle-instructions="__('messages.navigation_configure_handle_instructions')"
></ui-create-form>
@stop
