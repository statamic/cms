@extends('layout')

@section('content')

    <form method="post" action="{{ route('globals.store') }}">
        {!! csrf_field() !!}

        <div class="publish-form">
            <div class="flexy mb-24">
                <h1 class="fill">{{ translate('cp.create_global_set') }}</h1>
                <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
            </div>

            <div class="publish-fields card">

                <div class="form-group">
                    <label class="block">{{ translate('cp.title') }}</label>
                    <small class="help-block">{{ translate('cp.globals_title_instructions') }}</small>
                    <input type="text" name="title" class="form-control" autofocus="autofocus" />
                </div>

                <div class="form-group">
                    <label class="block">{{ translate('cp.slug') }}</label>
                    <small class="help-block">{!! translate('cp.globals_slug_instructions') !!}</small>
                    <input type="text" name="slug" class="form-control" />
                </div>

                <div class="form-group">
                    <label class="block">{{ translate('cp.fieldset') }}</label>
                    <small class="help-block">{{ translate('cp.globals_fieldset_instructions') }}</small>
                    <fieldset-fieldtype name="fieldset"></fieldset-fieldtype>
                </div>
            </div>

        </div>
    </form>

@endsection
