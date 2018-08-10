@extends('statamic::layout')
@section('content-class', 'dashboard')

@section('content')
    <div class="flexy mb-3">
        <h1 class="fill">{{ t('dashboard') }}</h1>
        @can('super')
            <a href="{{ route('settings.edit', 'cp')}}" class="btn btn-white">{{ t('manage_widgets') }}</a>
        @endcan
    </div>

    <div class="widgets">
        @foreach($widgets as $widget)
            <div class="widget {{ array_get($widget, 'width', 'half')}}">
                {!! $widget['html'] !!}
            </div>
        @endforeach
    </div>
@stop
