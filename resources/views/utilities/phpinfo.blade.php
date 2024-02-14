@extends('statamic::layout')
@section('title', Statamic\trans('PHP Info'))

@section('content')

    <header class="mb-6">
        @include('statamic::partials.breadcrumb', [
            'url' => cp_route('utilities.index'),
            'title' => Statamic\trans('Utilities')
        ])
        <h1>{{ Statamic\trans('PHP Info') }}</h1>
    </header>

    <div class="card p-0">
        <table class="data-table">
            <tr>
                <th class="pl-4 py-2 w-1/4">{{ Statamic\trans('PHP Version') }}</th>
                <td>{{ PHP_VERSION }}</td>
            </tr>
        </table>
    </div>

    @foreach ($phpinfo as $section => $items)
        <h2 class="mt-8 mb-2 font-bold text-lg">{{ $section }}</h2>
        <div class="card p-0">
            <table class="data-table">
                @foreach ($items as $name => $value)
                <tr>
                    <th class="pl-4 py-2 w-1/4">{{ $name }}</th>
                    <td class="break-all">{{ is_array($value) ? join(', ', $value) : $value }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    @endforeach

@stop
