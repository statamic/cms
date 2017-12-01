@extends('layout')

@section('content')

    <form method="post" action="{{ route('user.group', $group->uuid()) }}" class="card">
        {!! csrf_field() !!}

        <div class="head">
            <h1>{{ translate('cp.editing_usergroup') }}: <strong>{{ $group->title() }}</strong></h1>

            <button type="submit" class="btn btn-primary">{{ translate('cp.save') }}</button>
        </div>

        <hr>

        @include('usergroups.partials.form', [
            'group_title' => $group->title(),
            'group_slug' => $group->slug()
        ])

    </form>
@endsection
