@extends('statamic::layout')
@section('title', __('Dashboard'))

@section('content')

    <div class="widgets flex flex-wrap -mx-4___REPLACED py-2___REPLACED">
        @foreach($widgets as $widget)
            <div class="widget w-full md:{{ Statamic\Support\Str::tailwindWidthClass($widget['width']) }} {{ $widget['classes'] }} mb-8___REPLACED px-4___REPLACED">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Widgets'),
        'url' => Statamic::docsUrl('widgets')
    ])

@stop
