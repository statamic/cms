@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Create Fieldset'))
@section('content-card-modifiers', 'bg-architectural-lines')

@section('content')
<ui-create-form
    :title="__('Create Fieldset')"
    :subtitle="__('messages.fields_fieldsets_description')"
    icon="fieldsets"
    :route="'{{ cp_route('fieldsets.store') }}'"
    :title-instructions="__('messages.fieldsets_title_instructions')"
    :handle-instructions="__('messages.fieldsets_handle_instructions')"
></ui-create-form>
@stop
