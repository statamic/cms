@extends('statamic::layout')

@section('content')

    <form method="post" action="{{ route('collection.update', $collection->path()) }}">
        {!! csrf_field() !!}

            <div class="flexy mb-3">
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
                    <small class="help-block">{{ t('collection_fieldset_instructions') }}</small>
                    <fieldset-fieldtype name="fields[fieldset]" data="{{ $collection->get('fieldset') }}"></fieldset-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">{{ trans_choice('cp.templates', 1) }}</label>
                    <small class="help-block">{{ t('collection_template_instructions') }}</small>
                    <template-fieldtype name="fields[template]" data="{{ $collection->get('template') }}"></template-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">{{ t('route') }}</label>
                    <small class="help-block">{{ t('collection_route_instructions') }}</small>
                    <routes-fieldtype :data="{{ json_encode($routes) }}" name="routes"></routes-fieldtype>
                </div>

            </div>
        </div>
    </form>

@endsection
