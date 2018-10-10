@extends('statamic::layout')

@section('content')

    <form method="post" action="{{ route('user.role', $role->uuid()) }}">
        {!! csrf_field() !!}

        <div class="flex items-center mb-3 sticky">
            <h1 class="flex-1">{{ translate('cp.editing_role') }}</h1>
            <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
        </div>

        @include('statamic:roles.partials.form', [
            'role_title' => $role->title(),
            'role_slug' => $role->slug()
        ])

    </form>
@endsection
