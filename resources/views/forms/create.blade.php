@extends('statamic::layout')
@section('title', __('Create Form'))

@section('content')


        <formset-builder :create="true"
                         save-method="post"
                         save-url="{{ cp_route('forms.store') }}">
        </formset-builder>


@endsection
