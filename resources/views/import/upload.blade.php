@extends('statamic::layout')

@section('content')

    <form action="{{ route('import.upload') }}" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}

        <div class="flex items-center mb-3">
            <h1>{{ t('import_data') }}</h1>
        </div>

        <div class="card">
            <div class="flex items-center">
                <div class="form-group flex-1">
                    <label>{{ t('json_file') }}</label>
                    <small class="help-block"></small>
                    <input type="file" class="form-control" name="file" />
                </div>
                <button type="submit" class="btn btn-primary btn-lg ml-2">{{ t('import') }}</button>
            </div>
            <p class="help-block">
                <a href="https://docs.statamic.com/importer" target="_blank">{{ t('import_link_text')}} &raquo;</a>
            </p>

        </div>
    </form>

@stop
