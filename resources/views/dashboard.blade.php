@extends('layout')
@section('content-class', 'dashboard')

@section('content')
    <div class="flexy mb-24">
        <h1 class="fill">{{ __('Dashboard') }}</h1>
        <a href="{{ route('settings.edit', 'cp')}}" class="btn btn-white">{{ _('Manage Widgets') }}</a>
    </div>

    <div class="widgets">
        @foreach($widgets as $widget)
            <div class="widget {{ array_get($widget, 'width', 'half')}}">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div>
@stop
