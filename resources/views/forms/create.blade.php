@extends('statamic::layout')

@section('content')


        <formset-builder :create="true"
                         save-method="post"
                         save-url="{{ cp_route('forms.store') }}">
        </formset-builder>


@endsection
