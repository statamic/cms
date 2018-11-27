@extends('statamic::layout')
@section('title', __('PHP Info'))

@section('content')

    <h1>{{ __('PHP Info') }}</h1>

    <div class="card p-0 mt-4">
        <table class="data-table">
            <tr>
                <th class="pl-2 py-1 w-1/4">PHP Version</th>
                <td>{{ PHP_VERSION }}</td>
            </tr>
        </table>
    </div>

    @foreach ($phpinfo as $section => $items)
        <h2 class="mt-4 mb-2 font-bold text-xl">{{ $section }}</h2>
        <div class="card p-0">
            <table class="data-table">
                @foreach ($items as $name => $value)
                <tr>
                    <th class="pl-2 py-1 w-1/4">{{ $name }}</th>
                    <td>{{ is_array($value) ? join(', ', $value) : $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    @endforeach

@stop
