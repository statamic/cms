@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('PHP Info'))

@section('content')

<header class="mb-6">
    @include(
        'statamic::partials.breadcrumb',
        [
            'url' => cp_route('utilities.index'),
            'title' => __('Utilities'),
        ]
    )
    <h1>{{ __('PHP Info') }}</h1>
</header>

<div class="card p-0" dir="ltr">
    <table class="data-table ltr:text-left">
        <tr>
            <th class="w-1/4 py-2 ltr:pl-4 rtl:pr-4">{{ __('PHP Version') }}</th>
            <td>{{ PHP_VERSION }}</td>
        </tr>
    </table>
</div>

@foreach ($phpinfo as $section => $items)
    <h2 class="mb-2 mt-8 text-lg font-bold" dir="ltr">{{ $section }}</h2>
    <div class="card p-0" dir="ltr">
        <table class="data-table ltr:text-left">
            @foreach ($items as $name => $value)
                <tr>
                    <th class="w-1/4 py-2 ltr:pl-4 rtl:pr-4">{{ $name }}</th>
                    <td class="break-all">{{ is_array($value) ? join(', ', $value) : $value }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endforeach

@stop
