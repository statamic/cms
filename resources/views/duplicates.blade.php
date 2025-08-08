@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Duplicate IDs'))

@section('content')

<header class="mb-6">
    <div class="flex items-center justify-between">
        <h1>{{ __('Duplicate IDs') }}</h1>
    </div>
</header>

@if ($duplicates->isEmpty())
    <div class="card flex items-center">
        {{ __('No items with duplicate IDs.') }}
    </div>
@endif

@foreach ($duplicates as $id => $paths)
    <h6 class="mt-8">{{ $id }}</h6>

    <div class="card mt-2 p-0">
        <table class="data-table">
            @foreach ($paths as $path)
                <tr>
                    <td class="font-mono text-xs">
                        {{ $path }}
                    </td>
                    <td class="text-2xs ltr:text-right rtl:text-left">
                        <form method="POST" action="{{ cp_route('duplicates.regenerate') }}">
                            @csrf
                            <input type="hidden" name="path" value="{{ $path }}" />
                            <button class="text-blue">{{ __('Regenerate') }}</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>
@endforeach

@stop
