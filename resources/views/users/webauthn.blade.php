@extends('statamic::layout')
@section('title', __('Passkeys'))

@section('content')

    @include('statamic::partials.breadcrumb', [
        'url' => cp_route('users.index'),
        'title' => __('Users')
    ])

    <div class="flex mb-6">
        <h1 class="flex-1">{{ __('Passkeys') }}</h1>
    </div>

    <passkeys
        inline-template
        :web-authn-routes=@json([
            'options' => route('statamic.cp.webauthn.create-options'),
            'verify' => route('statamic.cp.webauthn.create'),
        ])
    >
    {{--
    <h6 class="mt-8">Site</h6>
    <div class="card p-0 mt-2">
        <table class="data-table">
            <tr>
                <td class="w-64 font-bold">
                    <span class="little-dot {{ $site->valid() ? 'bg-green-600' : 'bg-red-500' }} mr-2"></span>
                    {{ $site->key() ?? __('No license key') }}
                </td>
                <td class="relative">
                    {{ $site->domain()['url'] ?? '' }}
                    @if ($site->hasMultipleDomains())
                        <span class="text-2xs">({{ trans_choice('and :count more', $site->additionalDomainCount()) }})</span>
                    @endif
                </td>
                <td class="text-right text-red-500">{{ $site->invalidReason() }}</td>
            </tr>
        </table>
    </div>
    --}}

    <div class="mt-10 py-4 border-t flex items-center" v-show="showWebAuthn">
        <a class="btn btn-primary mr-4" @click="webAuthn">{{ __('Create Passkey') }}</a>
    </div>

    </passkeys>

@stop
