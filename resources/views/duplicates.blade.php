@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Duplicate IDs'))

@section('content')
<ui-header icon="duplicate" :title="__('Duplicate IDs')"></ui-header>

@if ($duplicates->isEmpty())
    <div
        class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-gray-500"
        v-text="__('No items with duplicate IDs.')"
    />
@endif

@foreach ($duplicates as $id => $paths)
    <ui-panel heading="{{ $id }}">
        <ui-card class="py-0!">
            <ui-table>
                @foreach ($paths as $path)
                    <ui-table-row>
                        <ui-table-cell class="font-mono">
                            {{ $path }}
                        </ui-table-cell>
                        <ui-table-cell class="flex items-center justify-end">
                            <form method="POST" action="{{ cp_route('duplicates.regenerate') }}">
                                @csrf
                                <input type="hidden" name="path" value="{{ $path }}" />
                                <ui-button size="sm">{{ __('Regenerate') }}</ui-button>
                            </form>
                        </ui-table-cell>
                    </ui-table-row>
                @endforeach
            </ui-table>
        </ui-card>
    </ui-panel>
@endforeach

@stop
