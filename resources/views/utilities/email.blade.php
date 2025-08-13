@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', Statamic::crumb(__('Email'), __('Utilities')))

@section('content')

<ui-header title="{{ __('Email') }}" icon="mail-settings" />

<ui-card-panel heading="{{ __('Send Test Email') }}">
    <form method="POST" action="{{ cp_route('utilities.email') }}">
        @csrf

        <div class="flex items-center gap-2">
            <ui-input
                name="email"
                value="{{ old('email', $user->email()) }}"
            ></ui-input>
            <ui-button
                text="{{ __('Send') }}"
                type="submit"
                variant="primary"
            ></ui-button>
        </div>
        @if ($errors->has('email'))
            <p class="mt-4 text-red-700 text-sm">{{ $errors->first('email') }}</p>
        @endif
    </form>
</ui-card-panel>

<ui-card-panel
    class="mt-6"
    heading="{{ __('Configuration') }}"
    subheading="{{ __('statamic::messages.email_utility_configuration_description', ['path' => config_path('mail.php')]) }}"
>
    <ui-table>
        <ui-table-rows>
            <ui-table-row class="[&_td:first-child]:font-medium">
                <ui-table-cell>{{ __('Default Mailer') }}</ui-table-cell>
                <ui-table-cell>{{ config('mail.default') }}</ui-table-cell>
            </ui-table-row>
            @if (config('mail.default') == 'smtp')
                <ui-table-row class="[&_td:fir]">
                    <ui-table-cell>{{ __('Host') }}</ui-table-cell>
                    <ui-table-cell>{{ config('mail.mailers.smtp.host') }}</ui-table-cell>
                </ui-table-row>
                <ui-table-row class="[&_td:fir]">
                    <ui-table-cell>{{ __('Port') }}</ui-table-cell>
                    <ui-table-cell>{{ config('mail.mailers.smtp.port') }}</ui-table-cell>
                </ui-table-row>
                <ui-table-row class="[&_td:fir]">
                    <ui-table-cell>{{ __('Encryption') }}</ui-table-cell>
                    <ui-table-cell>
                        @if (config('mail.mailers.smtp.encryption'))
                            {{ config('mail.mailers.smtp.encryption') }}
                        @endif
                    </ui-table-cell>
                </ui-table-row>
                <ui-table-row class="[&_td:fir]">
                    <ui-table-cell>{{ __('Username') }}</ui-table-cell>
                    <ui-table-cell>
                        @if (config('mail.mailers.smtp.username'))
                            {{ config('mail.mailers.smtp.username') }}
                        @endif
                    </ui-table-cell>
                </ui-table-row>
                <ui-table-row class="[&_td:fir]">
                    <ui-table-cell>{{ __('Password') }}</ui-table-cell>
                    <ui-table-cell>
                        @if (config('mail.mailers.smtp.password'))
                            {{ config('mail.mailers.smtp.password') }}
                        @endif
                    </ui-table-cell>
                </ui-table-row>
            @endif

            @if (config('mail.default') == 'sendmail')
                <ui-table-row class="[&_td:fir]">
                    <ui-table-cell>{{ __('Sendmail') }}</ui-table-cell>
                    <ui-table-cell>{{ config('mail.mailers.sendmail.path') }}</ui-table-cell>
                </ui-table-row>
            @endif

            <ui-table-row class="[&_td:fir]">
                <ui-table-cell>{{ __('Default From Address') }}</ui-table-cell>
                <ui-table-cell>
                    @if (config('mail.from.address'))
                        {{ config('mail.from.address') }}
                    @endif
                </ui-table-cell>
            </ui-table-row>
            <ui-table-row class="[&_td:fir]">
                <ui-table-cell>{{ __('Default From Name') }}</ui-table-cell>
                <ui-table-cell>
                    @if (config('mail.from.name'))
                        {{ config('mail.from.name') }}
                    @endif
                </ui-table-cell>
            </ui-table-row>
            <ui-table-row class="[&_td:fir]">
                <ui-table-cell>{{ __('Markdown theme') }}</ui-table-cell>
                <ui-table-cell>
                    @if (config('mail.markdown.theme'))
                        {{ config('mail.markdown.theme') }}
                    @endif
                </ui-table-cell>
            </ui-table-row>
            <ui-table-row class="[&_td:fir]">
                <ui-table-cell>{{ __('Markdown paths') }}</ui-table-cell>
                <ui-table-cell>
                    @foreach (config('mail.markdown.paths') as $path)
                        {{ $path }}
                        @if (! $loop->last)<br />@endif
                    @endforeach
                </ui-table-cell>
            </ui-table-row>
        </ui-table-rows>
    </ui-table>
</ui-card-panel>

@stop
