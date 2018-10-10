@extends('statamic::layout')

@section('content')

    <form method="post" action="{{ route('user.role.store') }}">
        {!! csrf_field() !!}

        <div class="flex items-center mb-3 w-full sticky" id="publish-controls">
            <h1 class="flex-1">{{ translate('cp.creating_role') }}</h1>
            <div class="controls">
                <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
            </div>
        </div>
        @include('statamic:roles.partials.form', ['role_title' => '', 'role_slug' => ''])
    </form>
@endsection
