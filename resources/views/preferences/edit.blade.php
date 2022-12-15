@extends('statamic::layout')
@section('title', __('Preferences'))

@section('content')

    <publish-form
        :title="__('Preferences')"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        action="/cp/preferences"
        method="patch"
        reload-on-save
    ></publish-form>

@stop
