@extends('layout')

@section('content')

    <div class="flexy mb-24">
        <h1 class="fill">Licensing</h1>
        <a href="{{ route('licensing.refresh') }}" class="btn btn-primary">Refresh</a>
    </div>

    @if (count($messages))
        <div class="alert alert-danger">
            <ul>
                @foreach ($messages as $message)
                    <li>{!! $message !!}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('licensing.update') }}">
        {{ csrf_field() }}

        <div class="card flush">

            <table class="dossier">
                <thead>
                    <tr>
                        <th class="checkbox-col"></th>
                        <th>{{ trans_choice('cp.items', 1) }}</th>
                        <th>{{ t('license_key') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr {{ $item['valid'] ? '' : 'class="text-danger"' }}>
                            <td>
                                @if ($item['valid'])
                                    <span class="icon icon-check text-success"></span>
                                @else
                                    <span class="icon icon-cross text-danger"></span>
                                @endif
                            </td>
                            <td>{{ $item['name'] }}</td>
                            <td>
                                <input type="text" class="form-control" name="{{ $item['id'] }}" value="{{ $item['key'] }}">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button class="btn btn-primary">{{ t('save') }}</button>

    </form>

@endsection
