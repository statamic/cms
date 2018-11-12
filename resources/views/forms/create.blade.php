@extends('statamic::layout')

@section('content')


        <formset-builder :create="true"
                         save-url="{{ cp_route('forms.store') }}">
        </formset-builder>


@endsection
