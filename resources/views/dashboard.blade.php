@extends('statamic::layout')

@section('content')

    <div class="flex items-center mb-3">
        <h1 class="flex-1">{{ __('Dashboard') }}</h1>
    </div>

    <div class="widgets">
        @foreach($widgets as $widget)
            <div class="widget {{ array_get($widget, 'width', 'half')}} mb-3">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div>

@stop
