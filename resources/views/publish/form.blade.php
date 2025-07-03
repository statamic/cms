@use('Statamic\Support\Str')

@extends('statamic::layout')

@section('content')
    <div class="@if ($usingConfigLayout) max-w-5xl mx-auto @endif">
        <ui-publish-form
            @if ($icon)icon="{{ $icon }}"@endif
            title="{{ $title }}"
            :blueprint="{{ Js::from($blueprint) }}"
            :initial-values="{{ Js::from($values) }}"
            :initial-meta="{{ Js::from($meta) }}"
            submit-url="{{ $submitUrl }}"
            submit-method="{{ $submitMethod }}"
            :read-only="{{ Js::from($readOnly) }}"
            :as-config="{{ Str::bool($usingConfigLayout) }}"
        ></ui-publish-form>
    </div>
@endsection
