@extends('statamic::layout')
@section('title', Statamic::crumb(__('Email'), __('Utilities')))

@section('content')

    <h1>
        <small class="subhead block">
            <a href="{{ cp_route('utilities.index')}}">{{ __('Utilities') }}</a>
        </small>
        {{ __('Email') }}
    </h1>

    <div class="mt-4 p-3 rounded shadow bg-white">
        <form method="POST" action="{{ cp_route('utilities.email') }}">
            @csrf

            <div class="flex items-center">
                <input class="input-text mr-2" type="text" name="email" value="{{ old('email', $user->email()) }}" />
                <button type="submit" class="btn btn-primary">{{ __('Send Test Email') }}</button>
            </div>
            @if ($errors->has('email'))
                <p class="mt-1"><small class="help-block text-red">{{ $errors->first('email') }}</small></p>
            @endif
        </form>
    </div>

    <h2 class="mt-4 mb-1 font-bold text-xl">{{ __('Configuration') }}</h2>
    <p class="text-sm text-grey mb-2">{!! __('statamic::messages.email_utility_configuration_description', ['path' => config_path('mail.php')]) !!}</p>
    <div class="card p-0">
        <table class="data-table">
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Driver') }}</th>
                <td><code>{{ config('mail.driver') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Host') }}</th>
                <td><code>{{ config('mail.host') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Port') }}</th>
                <td><code>{{ config('mail.port') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Default From Address') }}</th>
                <td><code>{{ config('mail.from.address') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Default From Name') }}</th>
                <td><code>{{ config('mail.from.name') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Encryption') }}</th>
                <td>
                    @if (config('mail.encryption'))
                        <code>{{ config('mail.encryption') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Username') }}</th>
                <td>
                    @if (config('mail.username'))
                        <code>{{ config('mail.username') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Password') }}</th>
                <td>
                    @if (config('mail.password'))
                        <code>{{ config('mail.password') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Sendmail') }}</th>
                <td><code>{{ config('mail.sendmail') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Markdown theme') }}</th>
                <td><code>{{ config('mail.markdown.theme') }}</code></td>
            </tr>
            <tr>
                <th class="pl-2 py-1 w-1/4">{{ __('Markdown paths') }}</th>
                <td>
                    @foreach (config('mail.markdown.paths') as $path)
                        <code>{{ $path }}</code><br>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

@stop
