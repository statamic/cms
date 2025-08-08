@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Form'))

@section('content')
    <ui-create-form
        :title="__('Create Form')"
        :subtitle="__('messages.form_configure_intro')"
        icon="forms"
        :route="'{{ cp_route('forms.store') }}'"
        :title-instructions="__('messages.form_configure_title_instructions')"
        :handle-instructions="__('messages.form_configure_handle_instructions')"
    ></ui-create-form>
@stop
