@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Dashboard'))

@section('content')

    <div class="widgets @container flex flex-wrap -mx-4 py-2">
        @foreach($widgets as $widget)
            <div class="widget w-full md:{{ Statamic\Support\Str::tailwindWidthClass($widget['width']) }} {{ $widget['classes'] }} mb-8 px-4">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Widgets'),
        'url' => Statamic::docsUrl('widgets')
    ])

@stop
