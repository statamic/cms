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
                            <td>{{ $passkey->lastLogin()?->format(config('statamic.cp.date_format')).' '.$passkey->lastLogin()?->format('H:i') }}

                            <td class="text-right text-red-500"><a class="btn-sm btn-danger" @click="(event) => deletePasskey('{{ $passkey->id() }}', event.target)">{{ __('Delete') }}</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
            @endif

            <div class="mt-10 py-4 border-t flex items-center" v-show="showWebAuthn">
                <a class="btn btn-primary mr-4" @click="webAuthn">{{ __('Create Passkey') }}</a>
            </div>

        </div>
    </passkeys>

@stop
