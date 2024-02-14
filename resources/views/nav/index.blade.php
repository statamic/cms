@extends('statamic::layout')
@section('title', Statamic\trans('CP Nav Preferences'))

@section('content')

    <div class="flex justify-between items-center mb-6">
        <h1>@yield('title')</h1>
    </div>

    <div class="card p-0 mb-4">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-4">@cp_svg('icons/light/earth')</div>
                        <a href="{{ cp_route('preferences.nav.default.edit') }}">{{ Statamic\trans('Default') }}</a>
                    </div>
                </td>
                <td class="text-right text-2xs text-gray-500">
                    @if (Statamic\Facades\Preference::default()->hasPreference('nav'))
                        {{ Statamic\trans('Modified') }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if (Statamic\Facades\Role::all()->isNotEmpty())
        <h3 class="little-heading pl-0 mb-2">{{ Statamic\trans('Override For Role') }}</h3>
        <div class="card p-0 mb-4">
            <table class="data-table">
                @foreach (Statamic\Facades\Role::all() as $role)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-4 h-4 mr-4">@cp_svg('icons/light/shield-key')</div>
                                <a href="{{ cp_route('preferences.nav.role.edit', [$role->handle()]) }}">{{ Statamic\trans($role->title()) }}</a>
                            </div>
                        </td>
                        <td class="text-right text-2xs text-gray-500">
                            @if ($role->hasPreference('nav'))
                                {{ Statamic\trans('Modified') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <h3 class="little-heading pl-0 mb-2">{{ Statamic\trans('Override For User') }}</h3>
    <div class="card p-0 mb-4">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-4">@cp_svg('icons/light/user')</div>
                    <a href="{{ cp_route('preferences.nav.user.edit') }}">{{ Statamic\trans('My Nav') }}</a>
                    </div>
                </td>
                <td class="text-right text-2xs text-gray-500">
                    @if (Statamic\Facades\User::current()->hasPreference('nav'))
                        {{ Statamic\trans('Modified') }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => Statamic\trans('Customizing the Control Panel Nav'),
        'url' => Statamic::docsUrl('customizing-the-cp-nav')
    ])
@endsection
