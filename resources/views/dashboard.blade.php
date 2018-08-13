@extends('statamic::layout')

@section('content')
    <div class="flexy mb-3">
        <h1 class="fill">{{ __('Dashboard') }}</h1>
    </div>

    {{-- <div class="widgets">
        @foreach($widgets as $widget)
            <div class="widget {{ array_get($widget, 'width', 'half')}}">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div> --}}
@stop
