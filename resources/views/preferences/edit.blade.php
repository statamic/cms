@extends('statamic::layout')
@section('title', $title)

@section('content')

    @if($showBreadcrumb)
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('preferences.index'),
            'title' => __('Preferences'),
        ])
    @endif

    <publish-form
        title="{{ $title }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        action="{{ $actionUrl }}"
        method="patch"
        reload-on-save
    ></publish-form>

@stop
