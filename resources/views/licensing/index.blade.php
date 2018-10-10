@extends('statamic::layout')

@section('content')

    <div class="md:flex justify-between items-center mb-3">
        <h1 class="flex-1">{{ t('license_keys') }}</h1>

        <a href="https://statamic.com/account/licenses" target="_blank" class="text-xs md:ml-2">
            {{ t('license_statamic_link') }} &rarr;
        </a>
    </div>

    <form method="POST" action="{{ route('licensing.update') }}">
        {{ csrf_field() }}

        <div class="card flush dossier-for-mobile">

            <table class="dossier">
                <thead>
                    <tr>
                        <th>{{ trans_choice('cp.items', 1) }}</th>
                        <th>{{ t('license_key') }}</th>
                        <th>{{ trans_choice('cp.statuses', 1) }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($licenses as $license)
                        <tr>
                            <td class="first-cell">{{ $license->name() }}</td>
                            <td width="320">
                                <input type="text" class="form-control font-mono text-xs" name="{{ $license->id() }}" value="{{ $license->key() }}">
                            </td>
                            <td class="text-xs mt-1 text-grey">
                                @if ($license->status())
                                    <span class="{{ $license->status()['status'] == 'positive' ? 'text-green' : 'text-red' }}">
                                    {{ $license->status()['message'] }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        <div>
            <button class="btn btn-primary">{{ t('save') }}</button>
            <a href="{{ route('licensing.refresh') }}" class="btn ml-1">{{ t('refresh') }}</a>
        </div>

    </form>

@endsection
