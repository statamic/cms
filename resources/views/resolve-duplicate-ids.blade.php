@extends('layout')

@section('content')

    <h1 class="mb-24">{{ t('duplicate_id_title')}}</h1>

    @if ($duplicates->isEmpty())
        <div class="card flexy">
            <span class="fill">{{ t('no_duplicate_ids')}}</span>
            <span>👍</span>
        </div>
    @else

        @foreach ($duplicates as $id => $paths)
            <div class="card flush">
                <div class="head">
                    <h2 class="m-0">{{ $id }}</h2>
                </div>
                <div class="card-body pad-16">
                    <table class="dossier">
                        @foreach ($paths as $path)
                            <tr>
                                <td>{{ $path }}</td>
                                <td class="column-actions">
                                    <form action="{{ route('resolve-duplicate-ids.update') }}" method="POST">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="path" value="{{ $path }}" />
                                        <button class="btn btn-default">Generate New ID</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        @endforeach

    @endif
</div>

@endsection
