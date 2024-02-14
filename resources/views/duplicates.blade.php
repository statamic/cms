@extends('statamic::layout')
@section('title', Statamic\trans('Duplicate IDs'))

@section('content')

    <header class="mb-6">
        <div class="flex items-center justify-between">
            <h1>{{ Statamic\trans('Duplicate IDs') }}</h1>
        </div>
    </header>

    @if ($duplicates->isEmpty())
        <div class="card flex items-center">
            {{ Statamic\trans('No items with duplicate IDs.') }}
        </div>
    @endif

    @foreach ($duplicates as $id => $paths)
        <h6 class="mt-8">{{ $id }}</h6>

        <div class="card p-0 mt-2">
            <table class="data-table">
                @foreach ($paths as $path)
                    <tr>
                        <td class="font-mono text-xs">
                            {{ $path }}
                        </td>
                        <td class="text-right text-2xs">
                            <form method="POST" action="{{ cp_route('duplicates.regenerate') }}">
                                @csrf
                                <input type="hidden" name="path" value="{{ $path }}" />
                                <button class="text-blue">{{ Statamic\trans('Regenerate') }}</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach


@stop
