@extends('statamic::layout')
@section('title', __('Clear Cache'))

@section('content')

    <h1>{{ __('Clear Cache') }}</h1>

    <div class="mt-4 p-3 rounded shadow bg-white">
        <form method="POST" action="{{ cp_route('utilities.clear-cache.clear') }}">
            @csrf
            @if ($errors->has('caches'))
                <p class="mb-1"><small class="help-block text-red">{{ $errors->first() }}</small></p>
            @endif
            <label class="mb-1"><input type="checkbox" name="caches[]" value="cache" class="mr-1">Application Cache</label>
            <label class="mb-1"><input type="checkbox" name="caches[]" value="stache" class="mr-1">Stache Datastore</label>
            <label class="mb-1"><input type="checkbox" name="caches[]" value="static" class="mr-1">Static Page Cache</label>
            <label class="mb-1"><input type="checkbox" name="caches[]" value="glide" class="mr-1">Glide Image Cache</label>
            <button type="submit" class="btn btn-primary mt-1">Clear</button>
        </form>
    </div>

@stop
