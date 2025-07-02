@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('CP Nav Preferences'))

@section('content')
    <ui-header title="{{ __('CP Nav Preferences') }}"></ui-header>

    <section class="space-y-6">
        <ui-card-panel heading="{{ __('Global Preferences') }}">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <ui-icon name="globals" />
                    <a href="{{ cp_route('preferences.nav.default.edit') }}">{{ __('Default') }}</a>
                </div>

                @if (Statamic\Facades\Preference::default()->hasPreference('nav'))
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
                            <a href="{{ cp_route('preferences.nav.role.edit', [$role->handle()]) }}">
                                {{ __($role->title()) }}
                            </a>
                        </div>
                        @if ($role->hasPreference('nav'))
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
                    <a href="{{ cp_route('preferences.nav.user.edit') }}">{{ __('My Nav') }}</a>
                </div>

                @if (Statamic\Facades\User::current()->hasPreference('nav'))
                    <ui-badge color="green">{{ __('Modified') }}</ui-badge>
                @endif
            </div>
        </ui-card-panel>
    </section>

    <x-statamic::docs-callout
        :topic="__('Customizing the Control Panel Nav')"
        :url="Statamic::docsUrl('customizing-the-cp-nav')"
    />
@endsection
