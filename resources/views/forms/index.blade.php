@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Forms'))

@section('content')
    <ui-header title="{{ __('Forms') }}" icon="forms">
        @if (Statamic::pro() && $user->can('create', 'Statamic\Contracts\Forms\Form'))
            <ui-button href="{{ cp_route('forms.create') }}" text="{{ __('Create Form') }}" variant="primary" />
        @endif
    </ui-header>

    <form-listing
        :items="{{ json_encode($forms) }}"
        :initial-columns="{{ json_encode($initialColumns) }}"
        action-url="{{ $actionUrl }}"
    ></form-listing>

    <x-statamic::docs-callout topic="{{ __('Forms') }}" url="{{ Statamic::docsUrl('forms') }}" />
@endsection
