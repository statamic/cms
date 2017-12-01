@extends('layout')

@section('content')

    <form method="post" action="{{ route('collection.update', $collection->path()) }}">
        {!! csrf_field() !!}

            <div class="flexy mb-24">
                <h1 class="fill">{{ t('thing_configure', ['thing' => $collection->title()]) }}</h1>
                <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
            </div>

            <div class="card">

                <div class="form-group">
                    <label class="block">{{ t('title') }}</label>
                    <small class="help-block">{{ t('collection_title_instructions') }}</small>
                    <input type="text" name="fields[title]" class="form-control" value="{{ $collection->title() }}" />
                </div>

                <div class="form-group">
                    <label class="block">{{ t('fieldset') }}</label>
                    <fieldset-fieldtype name="fields[fieldset]" data="{{ $collection->get('fieldset') }}"></fieldset-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">{{ t('route') }}</label>
                    <small class="help-block">{{ t('collection_route_instructions') }}</small>
                    <input type="text" name="fields[route]" class="form-control" value="{{ $collection->route() }}" />
                </div>

            </div>
        </div>
    </form>

@endsection
