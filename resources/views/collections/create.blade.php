@extends('statamic::layout')

@section('content')
    <collection-wizard
        route="{{ cp_route('collections.store') }}">
    </collection-wizard>
@stop

@section('nontent')

    <form method="POST" action="{{ cp_route('collections.store') }}">
        @csrf

        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ __('Create Collection') }}</h1>
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
        </div>

        <div class="publish-form card">

            <div class="form-group">
                <label class="block">{{ __('Title') }}</label>
                <small class="help-block">{{ __('The proper name of your collection.') }}</small>
                <input type="text" name="title" class="input-text" value="{{ old('title') }}" autofocus="autofocus">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Handle') }}</label>
                <small class="help-block">{{ __("The collection's variable name used in settings and templates.") }}</small>
                <input type="text" name="handle" class="input-text" value="{{ old('handle') }}">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Order') }}</label>
                <small class="help-block">{{ __('Set the default sorting method for entries in this collection.' )}}</small>
                {{-- TODO: Bring back select fieldtype. --}}
                <select name="order" class="input-text">
                    <option value="alphabetical" {{ old('order') == 'alphabetical' ? 'selected' : '' }}>Alphabetical</option>
                    <option value="date" {{ old('order') == 'date' ? 'selected' : '' }}>Date</option>
                    <option value="number" {{ old('order') == 'number' ? 'selected' : '' }}>Number</option>
                </select>
            </div>

            <div class="form-group">
                <label class="block">{{ __('Fieldset') }}</label>
                <small class="help-block">{{ __('The default fieldset, unless otherwise specified.') }}</small>
                {{-- TODO: Bring back fieldset fieldtype. --}}
                <input type="text" name="fieldset" class="input-text" value="{{ old('fieldset') }}">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Template') }}</label>
                <small class="help-block">{{ __('The default template, unless otherwise specified.') }}</small>
                {{-- TODO: Bring back template fieldtype. --}}
                <input type="text" name="template" class="input-text" value="{{ old('template') }}">
            </div>

            <div class="form-group">
                <label class="block">{{ __('Route') }}</label>
                <small class="help-block">{{ __('The route controls the URL pattern all entries in the collection will follow.') }}</small>
                {{-- TODO: Bring back routes fieldtype. --}}
                <input type="text" name="route" class="input-text" value="{{ old('route') }}">
            </div>

        </div>
    </form>

@endsection
