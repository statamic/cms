@extends('statamic::layout')

@section('content')

    <fieldset-builder :create="true"
                      save-url="{{ route('fieldset.store') }}">
    </fieldset-builder>

@endsection