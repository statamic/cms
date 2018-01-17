@extends('layout')

@section('content')


        <formset-builder :create="true"
                         save-url="{{ route('form.store') }}">
        </formset-builder>


@endsection
