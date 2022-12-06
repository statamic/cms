@extends('statamic::layout')
@section('title', __('Nav Customizer'))

@section('content')

    <div class="flex justify-between items-center mb-3">
        <h1>@yield('title')</h1>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Default') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-2">@cp_svg('earth')</div>
                        <a href="{{ cp_route('nav-preferences.edit') }}">{{ __('Global Default') }}</a>
                    </div>
                </td>
                <td class="text-right text-2xs"><a href="{{ cp_route('nav-preferences.edit') }}" class="text-blue">Customize</a></td>
            </tr>
        </table>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Override Per Role') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-2">@cp_svg('shield-key')</div>
                        <a class="text-grey-70" href="{{ cp_route('nav-preferences.edit') }}">{{ __('Super Admin') }}</a>
                    </div>
                </td>
                <td class="text-right text-2xs">Not Set</td>
            </tr>
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-2">@cp_svg('shield-key')</div>
                        <a href="{{ cp_route('nav-preferences.edit') }}">{{ __('Editor') }}</a>
                    </div>
                </td>
                <td class="text-right text-2xs"><a href="{{ cp_route('nav-preferences.edit') }}" class="text-blue">Customize</a></td>
            </tr>
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-2">@cp_svg('shield-key')</div>
                        <a href="{{ cp_route('nav-preferences.edit') }}">{{ __('Social Media Manager') }}</a>
                    </div>
                </td>
                <td class="text-right text-2xs"><a href="{{ cp_route('nav-preferences.edit') }}" class="text-blue">Customize</a></td>
            </tr>
        </table>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Override Per User') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-2">@cp_svg('user')</div>
                        <a href="{{ cp_route('nav-preferences.edit') }}">You</a>
                    </div>
                </td>
                <td class="text-right text-2xs"><a href="{{ cp_route('nav-preferences.edit') }}" class="text-blue">Customize</a></td>
            </tr>
        </table>
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Customizing the Control Panel Nav'),
        'url' => Statamic::docsUrl('customizing-the-cp-nav')
    ])
@endsection
