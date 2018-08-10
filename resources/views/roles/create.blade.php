@extends('statamic::layout')

@section('content')

    <form method="post" action="{{ route('user.role.store') }}">
        {!! csrf_field() !!}

        <div class="flexy mb-3 full-width sticky" id="publish-controls">
            <h1 class="fill">{{ translate('cp.creating_role') }}</h1>
            <div class="controls">
                <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
            </div>
        </div>
        @include('statamic:roles.partials.form', ['role_title' => '', 'role_slug' => ''])
    </form>
@endsection
