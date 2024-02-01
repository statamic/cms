@extends('statamic::layout')
@section('title', __('Preferences'))

@section('content')

    <div class="flex justify-between items-center mb-6">
        <h1>@yield('title')</h1>
    </div>

    <div class="card p-0 mb-4">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/earth')</div>
                        <a href="{{ cp_route('preferences.default.edit') }}">{{ __('Default') }}</a>
                    </div>
                </td>
                <td class="rtl:text-left ltr:text-right text-2xs text-gray-500">
                    @if (!empty(Statamic\Facades\Preference::default()->all()))
                        {{ __('Modified') }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if (Statamic\Facades\Role::all()->isNotEmpty())
        <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Override For Role') }}</h3>
        <div class="card p-0 mb-4">
            <table class="data-table">
                @foreach (Statamic\Facades\Role::all() as $role)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/shield-key')</div>
                                <a href="{{ cp_route('preferences.role.edit', [$role->handle()]) }}">{{ __($role->title()) }}</a>
                            </div>
                        </td>
                        <td class="rtl:text-left ltr:text-right text-2xs text-gray-500">
                            @if (!empty($role->preferences()))
                                {{ __('Modified') }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Override For User') }}</h3>
    <div class="card p-0 mb-4">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/user')</div>
                        <a href="{{ cp_route('preferences.user.edit') }}">{{ __('My Preferences') }}</a>
                    </div>
                </td>
                <td class="rtl:text-left ltr:text-right text-2xs text-gray-500">
                    @if (!empty(Statamic\Facades\User::current()->preferences()))
                        {{ __('Modified') }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Preferences'),
        'url' => Statamic::docsUrl('preferences')
    ])
@endsection
