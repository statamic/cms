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

    <passkeys inline-template :web-authn-routes=@json($routes)>
        <div>

            @if ($passkeys->isEmpty())
            <p class="text-sm text-gray mt-2">{{ __('No passkeys created') }}</p>
            @else
            <div class="card p-0 mt-10">
                <table class="data-table">
                    @foreach ($passkeys as $passkey)
                        <tr>
                            <td class="w-128 mr-2">
                                <span class="little-dot bg-green-600 mr-2"></span>
                                <span class="font-bold">{{ $passkey->id() }}</span>
                                <span class="badge uppercase font-bold text-gray-600">{{ $passkey->get('type') }}</span>
                            </td>
                            <td>{{ ($login = $passkey->lastLogin()) ? ($login->format(config('statamic.cp.date_format')).' '.$login->format('H:i')) : __('Never') }}

                            <td class="text-right text-red-500"><a class="btn-sm btn-danger" @click="(event) => deletePasskey('{{ $passkey->id() }}', event.target)">{{ __('Delete') }}</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
            @endif

            <div class="mt-10 py-4 border-t flex items-center" v-show="showWebAuthn">
                <a class="btn btn-primary mr-4" @click="webAuthn">{{ __('Create Passkey') }}</a>
            </div>

            <modal name="passkey-create-error" v-if="showErrorModal">
                <div class="confirmation-modal flex flex-col h-full">
                    <div class="text-lg font-medium p-4 pb-0">
                        {{ __('There was an error creating your passkey') }}
                    </div>
                    <div class="flex-1 px-4 py-6 text-gray">
                        <p class="mb-4" v-text="error" />
                    </div>
                    <div class="p-4 bg-gray-200 border-t flex items-center justify-end text-sm">
                        <button class="text-gray hover:text-gray-900"
                            @click="error = false"
                            v-text="__('Close')" />
                    </div>
                </div>
            </modal>

        </div>

    </passkeys>

@stop
