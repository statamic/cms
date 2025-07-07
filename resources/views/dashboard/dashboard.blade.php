@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Dashboard'))

@section('content')

<ui-header title="{{ __('Dashboard') }}" icon="dashboard" />

<div class="widgets @container/widgets flex flex-wrap py-2 gap-y-6 -mx-3">
    @foreach ($widgets as $widget)
        <div class="{{ Statamic\Support\Str::tailwindWidthClass($widget['width']) }} {{ $widget['classes'] }} px-3">
            {!! $widget['html'] !!}
        </div>
    @endforeach
</div>

    <x-statamic::docs-callout
        :topic="__('Widgets')"
        :url="Statamic::docsUrl('widgets')"
    />

@stop
