@extends('layout')

@section('content')


        <formset-builder formset-title="{{ $form->title() }}"
                         formset-name="{{ $form->name() }}"
                         save-url="{{ route('form.update', $form->name()) }}">
        </formset-builder>


@endsection
