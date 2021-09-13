@extends('statamic::layout')
@section('title', __('PHP Info'))

@section('content')

    <header class="mb-3">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities')
        ])
        <h1>{{ __('PHP Info') }}</h1>
    </header>

    <div class="card p-0">
        <table class="data-table">
            <tr>
                <th class="pl-2 py-1 w-1/4">PHP Version</th>
                <td>{{ PHP_VERSION }}</td>
            </tr>
        </table>
    </div>

    @foreach ($phpinfo as $section => $items)
        <h2 class="mt-4 mb-1 font-bold text-lg">{{ $section }}</h2>
        <div class="card p-0">
            <table class="data-table">
                @foreach ($items as $name => $value)
                <tr>
                    <th class="pl-2 py-1 w-1/4">{{ $name }}</th>
                    <td class="break-all">{{ is_array($value) ? join(', ', $value) : $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    @endforeach

@stop
