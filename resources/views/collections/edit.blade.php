@extends('statamic::layout')

@section('content')

    <form method="POST" action="{{ cp_route('collections.update', $collection->path()) }}">
        @method('patch') @csrf

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ $collection->title() }}</h1>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>

        <div class="publish-form card">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('The proper name of your collection.') }}</small>
                <input type="text" name="title" class="input-text" value="{{ old('title', $collection->get('title')) }}" autofocus="autofocus">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Fieldset') }}</label>
                <small class="help-block">{{ __('The default fieldset, unless otherwise specified.') }}</small>
                {{-- TODO: Bring back fieldset fieldtype. --}}
                <input type="text" name="fieldset" class="input-text" value="{{ old('fieldset', $collection->get('fieldset')) }}">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Template') }}</label>
                <small class="help-block">{{ __('The default template, unless otherwise specified.') }}</small>
                {{-- TODO: Bring back template fieldtype. --}}
                <input type="text" name="template" class="input-text" value="{{ old('template', $collection->get('template')) }}">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Route') }}</label>
                <small class="help-block">{{ __('The route controls the URL pattern all entries in the collection will follow.') }}</small>
                {{-- TODO: Bring back routes fieldtype. --}}
                <input type="text" name="route" class="input-text" value="{{ old('route', $collection->get('route')) }}">
            </div>

        </div>
    </form>

@endsection
