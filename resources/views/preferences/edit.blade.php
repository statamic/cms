@extends('statamic::layout')
@section('title', $title)

@section('content')

    @if($showBreadcrumb)
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('preferences.index'),
            'title' => __('Preferences'),
        ])
    @endif

    <preferences-edit-form
        title="{{ $title }}"
        :blueprint='@json($blueprint)'
        :meta='@json($meta)'
        :values='@json($values)'
        action="{{ $actionUrl }}"
        :save-as-options="{{ json_encode($saveAsOptions) }}"
    ></preferences-edit-form>

@stop
