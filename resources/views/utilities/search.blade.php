@extends('statamic::layout')
@section('title', __('Rebuild Search'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <h1>{{ __('Update Search Indexes') }}</h1>
    </header>

    <div class="card">
        <form method="POST" action="{{ cp_route('utilities.search') }}">
            @csrf

            @if ($errors->has('indexes'))
                <p class="mb-1"><small class="help-block text-red">{{ $errors->first() }}</small></p>
            @endif

            @foreach (\Statamic\Facades\Search::indexes() as $index)
                <label class="mb-1">
                    <input type="checkbox" name="indexes[]" value="{{ $index->name() }}" class="mr-1">
                    {{ $index->title() }}
                </label>
            @endforeach

            <button type="submit" class="btn-primary mt-1">{{ __('Update') }}</button>
        </form>
    </div>

@stop
