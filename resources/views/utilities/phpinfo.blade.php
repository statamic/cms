@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('PHP Info'))

@section('content')

<ui-header title="{{ __('PHP Info') }}"></ui-header>

<section class="space-y-6">
    <ui-card-panel heading="{{ __('PHP version') }}">
        <ui-table>
            <ui-table-row>
                <ui-table-cell width="30%">{{ __('PHP Version') }}</ui-table-cell>
                <ui-table-cell>{{ PHP_VERSION }}</ui-table-cell>
            </ui-table-row>
        </ui-table>
    </ui-card-panel>

    @foreach ($phpinfo as $section => $items)
        <ui-card-panel heading="{{ $section }}">
            <ui-table>
                @foreach ($items as $name => $value)
                    <ui-table-row>
                        <ui-table-cell width="30%">{{ $name }}</ui-table-cell>
                        <ui-table-cell>{{ is_array($value) ? join(', ', $value) : $value }}</ui-table-cell>
                    </ui-table-row>
                @endforeach
            </ui-table>
        </ui-card-panel>
    @endforeach
</section>

@stop
