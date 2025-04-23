@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Dashboard'))

@section('content')

<div class="widgets @container -mx-4 flex flex-wrap py-2">
    @foreach ($widgets as $widget)
        <div
            class="widget md:{{ Statamic\Support\Str::tailwindWidthClass($widget['width']) }} {{ $widget['classes'] }} mb-8 w-full px-4"
        >
            {!! $widget['html'] !!}
        </div>
    @endforeach
</div>

@include(
    'statamic::partials.docs-callout',
    [
        'topic' => __('Widgets'),
        'url' => Statamic::docsUrl('widgets'),
    ]
)

@stop
