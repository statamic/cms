@extends('statamic::layout')
@section('content-class', 'publishing')

@section('content')


        <formset-builder
                         :initial-formset="{{ $formset }}"
                         formset-title="{{ $form->title() }}"
                         formset-name="{{ $form->handle() }}"
                         save-method="patch"
                         save-url="{{ cp_route('forms.update', $form->handle()) }}">
        </formset-builder>


@endsection
