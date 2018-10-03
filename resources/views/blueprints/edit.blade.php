@extends('statamic::layout')

@section('content')

    <blueprint-builder
        action="{{ cp_route('blueprints.update', $blueprint->handle()) }}"
        :initial-blueprint="{{ json_encode($blueprintVueObject) }}"
    ></blueprint-builder>

@endsection
