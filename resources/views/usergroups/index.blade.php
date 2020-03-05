@extends('statamic::layout')
@section('title', __('User Groups'))

@section('content')

    @unless($groups->isEmpty())

        <div class="flex mb-3">
            <h1 class="flex-1">
                {{ __('User Groups') }}
            </h1>
            <a href="{{ cp_route('user-groups.create') }}" class="btn-primary">{{ __('Create User Group') }}</a>
        </div>

        <user-group-listing :initial-rows="{{ json_encode($groups) }}"></user-group-listing>

    @else

        @include('statamic::partials.create-first', [
            'resource' => 'User Group',
            'description' => 'User groups allow you to create permission groupings to remove the tedium of assigning multiple permissions to users.',
            'svg' => 'empty/collection', // TODO: Need empty/user-group svg
            'route' => cp_route('user-groups.create')
        ])

    @endunless

@endsection
