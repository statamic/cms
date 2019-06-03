@extends('statamic::layout')
@section('title', crumb($form->title(), 'Forms'))

@section('content')

    <div class="flex mb-3">
        <h1 class="flex-1">
            <small class="subhead block">
                <a href="{{ cp_route('forms.index')}}">{{ __('Forms') }}</a>
            </small>
            {{ $form->title() }}
        </h1>

        <a class="btn" href="{{ cp_route('forms.edit', $form->handle()) }}">{{ __('Edit') }}</a>
        <a class="btn ml-1" href="{{ cp_route('forms.export', ['type' => 'csv', 'form' => $form->handle()]) }}?download=true">{{ __('Export CSV') }}</a>
        <a class="btn ml-1" href="{{ cp_route('forms.export', ['type' => 'json', 'form' => $form->handle()]) }}?download=true">{{ __('Export JSON') }}</a>
    </div>

    @if (! empty($form->metrics()))
    <div class="metrics mb-3">
        @foreach($form->metrics() as $metric)
            <div class="card px-3">
                <h3 class="mb-2 font-bold text-grey">{{ $metric->label() }}</h3>
                <div class="text-4xl">{{ $metric->result() }}</div>
            </div>
        @endforeach
    </div>
    @endif

    <form-submission-listing form="{{ $form->handle() }}" v-cloak>

        <div slot="no-results" class="text-center border-2 border-dashed rounded-lg">
            <div class="max-w-md mx-auto px-4 py-8">
                @svg('empty/form')
                <h1 class="my-3">{{ __('No submissions') }}</h1>
            </div>
        </div>

    </form-submission-listing>

@endsection
