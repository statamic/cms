@extends('layout')

@section('content')

    <form method="post" action="{{ route('collection.store') }}">
        {!! csrf_field() !!}

        <div class="flexy mb-24">
            <h1 class="fill">{{ translate('cp.create_collection') }}</h1>
            <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
        </div>

        <div class="publish-form card">

            <div class="publish-fields">

                <div class="form-group">
                    <label class="block">{{ t('title') }}</label>
                    <small class="help-block">{{ t('collection_title_instructions') }}</small>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" autofocus="autofocus">
                </div>

                <div class="form-group">
                    <label class="block">{{ t('handle') }}</label>
                    <small class="help-block">{{ t('collection_handle_instructions') }}</small>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
                </div>

                <div class="form-group">
                    <label class="block">{{ t('order') }}</label>
                    <small class="help-block">{{ t('collection_order_instructions' )}}</small>
                    <select-fieldtype name="order" data="{{ old('order') }}" :options='[
                        {"value": "", "text": "Alphabetical"},
                        {"value": "date", "text": "Date"},
                        {"value": "number", "text": "Number"}
                    ]'></select-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">{{ t('fieldset') }}</label>
                    <fieldset-fieldtype name="fieldset" data="{{ old('fieldset') }}"></fieldset-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">{{ t('route') }}</label>
                    <small class="help-block">{{ t('collection_route_instructions') }}</small>
                    <input type="text" name="route" class="form-control" value="{{ old('route') }}">
                </div>

            </div>
        </div>
    </form>

@endsection
