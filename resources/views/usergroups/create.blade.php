@extends('layout')

@section('content')

    <form method="post" action="{{ route('user.group.store') }}" class="card">
        {!! csrf_field() !!}

        <div class="head">
            <h1>{{ translate('cp.creating_usergroup') }}</h1>

            <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
        </div>

        <hr>

        @include('usergroups.partials.form', ['group_title' => '', 'group_slug' => ''])

    </form>
@endsection
