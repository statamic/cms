@extends('statamic::layout')

@section('content')

    <form method="post" action="{{ route('taxonomy.update', $group->path()) }}">
        {!! csrf_field() !!}

        <div class="publish-form">
            <div class="flex items-center mb-3">
                <h1 class="flex-1">{{ translate('cp.editing_taxonomy') }}</h1>
                <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
            </div>

            <div class="publish-fields card">

                <div class="form-group">
                    <label class="block">{{ t('title') }}</label>
                    <small class="help-block">{{ t('taxonomies_title_instructions') }}</small>
                    <input type="text" name="title" class="input-text" value="{{ $group->title() }}" />
                </div>

                <div class="form-group">
                    <label class="block">{{ t('fieldset') }}</label>
                    <small class="help-block">{{ t('taxonomies_fieldset_instructions') }}</small>
                    <fieldset-fieldtype name="fieldset" data="{{ $group->get('fieldset') }}"></fieldset-fieldtype>
                </div>

                <div class="form-group">
                    <label class="block">{{ t('route') }}</label>
                    <small class="help-block">{{ t('taxonomies_route_instructions') }}</small>
                    <input type="text" name="route" class="input-text" value="{{ $group->route() }}" />
                </div>

            </div>

        </div>
    </form>

@endsection
