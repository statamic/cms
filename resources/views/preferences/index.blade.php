@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Preferences'))

@section('content')
    <ui-header :title="__('Preferences')" icon="preferences" />

    <section class="space-y-6">
        <ui-card-panel heading="{{ __('Global Preferences') }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <ui-icon name="globals" />
                    <a href="{{ cp_route('preferences.default.edit') }}">{{ __('Default') }}</a>
                </div>

                @if (! empty(Statamic\Facades\Preference::default()->all()))
                    <ui-badge color="green">{{ __('Modified') }}</ui-badge>
                @endif
            </div>
        </ui-card-panel>

        @if (Statamic\Facades\Role::all()->isNotEmpty())
            <ui-card-panel heading="{{ __('Preferences by Role') }}">
                @foreach (Statamic\Facades\Role::all() as $role)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <ui-icon name="permissions" />
                            <a href="{{ cp_route('preferences.role.edit', [$role->handle()]) }}">
                                {{ __($role->title()) }}
                            </a>
                        </div>
                        @if (! empty($role->preferences()))
                            <ui-badge color="green">{{ __('Modified') }}</ui-badge>
                        @endif
                    </div>
                @endforeach
            </ui-card-panel>
        @endif

        <ui-card-panel heading="{{ __('User Preferences') }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <ui-icon name="avatar" />
                    <a href="{{ cp_route('preferences.user.edit') }}">{{ __('My Preferences') }}</a>
                </div>

                @if (! empty(Statamic\Facades\User::current()->preferences()))
                    <ui-badge color="green">{{ __('Modified') }}</ui-badge>
                @endif
            </div>
        </ui-card-panel>
    </section>

    <x-statamic::docs-callout :topic="__('Preferences')" :url="Statamic::docsUrl('preferences')" />
@endsection
