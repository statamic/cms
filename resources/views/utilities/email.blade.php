@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', Statamic::crumb(__('Email'), __('Utilities')))

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <h1>{{ __('Email') }}</h1>
    </header>

    <div class="card">
        <form method="POST" action="{{ cp_route('utilities.email') }}">
            @csrf

            <div class="flex items-center">
                <input class="input-text rtl:ml-4 ltr:mr-4" type="text" name="email" value="{{ old('email', $user->email()) }}" />
                <button type="submit" class="btn-primary">{{ __('Send Test Email') }}</button>
            </div>
            @if ($errors->has('email'))
                <p class="mt-2"><small class="help-block text-red-500">{{ $errors->first('email') }}</small></p>
            @endif
        </form>
    </div>

    <h2 class="mt-10 mb-2 font-bold text-lg">{{ __('Configuration') }}</h2>
    <p class="text-sm text-gray mb-4">{!! __('statamic::messages.email_utility_configuration_description', ['path' => config_path('mail.php')]) !!}</p>
    <div class="card p-0">
        <table class="data-table">
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Default Mailer') }}</th>
                <td><code>{{ config('mail.default') }}</code></td>
            </tr>
            @if (config('mail.default') == 'smtp')
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Host') }}</th>
                <td><code>{{ config('mail.mailers.smtp.host') }}</code></td>
            </tr>
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Port') }}</th>
                <td><code>{{ config('mail.mailers.smtp.port') }}</code></td>
            </tr>
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Encryption') }}</th>
                <td>
                    @if (config('mail.mailers.smtp.encryption'))
                        <code>{{ config('mail.mailers.smtp.encryption') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Username') }}</th>
                <td>
                    @if (config('mail.mailers.smtp.username'))
                        <code>{{ config('mail.mailers.smtp.username') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Password') }}</th>
                <td>
                    @if (config('mail.mailers.smtp.password'))
                        <code>{{ config('mail.mailers.smtp.password') }}</code>
                    @endif
                </td>
            </tr>
            @endif
            @if (config('mail.default') == 'sendmail')
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Sendmail') }}</th>
                <td><code>{{ config('mail.mailers.sendmail.path') }}</code></td>
            </tr>
            @endif
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Default From Address') }}</th>
                <td>
                    @if (config('mail.from.address'))
                        <code>{{ config('mail.from.address') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Default From Name') }}</th>
                <td>
                    @if (config('mail.from.name'))
                        <code>{{ config('mail.from.name') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Markdown theme') }}</th>
                <td>
                    @if (config('mail.markdown.theme'))
                        <code>{{ config('mail.markdown.theme') }}</code>
                    @endif
                </td>
            </tr>
            <tr>
                <th class="rtl:pr-4 ltr:pl-4 py-2 w-1/4">{{ __('Markdown paths') }}</th>
                <td>
                    @foreach (config('mail.markdown.paths') as $path)
                        <code>{{ $path }}</code><br>
                    @endforeach
                </td>
            </tr>
        </table>
    </div>

@stop
