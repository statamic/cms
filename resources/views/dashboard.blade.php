@extends('statamic::layout')
@section('title', __('Dashboard'))

@section('content')

    <div class="widgets flex flex-wrap -mx-2 py-1">
        @foreach($widgets as $widget)
            <div class="widget w-full md:{{ Statamic\Support\Str::tailwindWidthClass($widget['width']) }} {{ $widget['classes'] }} mb-4 px-2">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Widgets'),
        'url' => Statamic::docsUrl('widgets')
    ])

@stop
