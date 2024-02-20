@extends('statamic::layout')
@section('content-class', 'publishing')
@section('title', __('Settings'))

@section('content')

    <script>
        Statamic.Publish = {
            contentData: {!! json_encode($content_data) !!},
            fieldset: {!! json_encode($fieldset) !!},
        };
    </script>

    <publish title="{{ $title }}"
             extra="{{ json_encode($extra) }}"
             :is-new="false"
             slug="{{ $slug }}"
             content-type="{{ $content_type }}"
             :update-title-on-save="false"
    ></publish>

@stop
