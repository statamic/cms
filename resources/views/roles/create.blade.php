@extends('layout')

@section('content')

    <form method="post" action="{{ route('user.role.store') }}">
        {!! csrf_field() !!}

        <div class="card sticky">
            <div class="head">
            <h1>{{ translate('cp.creating_role') }}</h1>

            <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
            </div>
        </div>

        @include('roles.partials.form', ['role_title' => '', 'role_slug' => ''])

    </form>
@endsection
