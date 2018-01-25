@extends('statamic::layout')
@section('content-class', 'dashboard')

@section('content')
    <div class="flexy mb-24">
        <h1 class="fill">{{ __('Dashboard') }}</h1>
    </div>

    @if ($widgets->isEmpty())
        <p>{{ __('No widgets. Configure widgets in config/cp.php') }}</p>
    @else
        <div class="widgets">
            @foreach($widgets as $widget)
                <div class="widget {{ array_get($widget, 'width', 'half')}}">
                    {!! $widget['html'] !!}
                </div>
            @endforeach
        </div>
    @endif
@stop
