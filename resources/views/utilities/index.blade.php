@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Utilities'))

@section('content')
    <ui-header title="{{ __('Utilities') }}" icon="utilities" />

    <ui-card-panel>
        <div class="flex flex-wrap">
            @foreach ($utilities as $utility)
                <a
                    href="{{ $utility->url() }}"
                    class="group w-full items-start rounded-md border border-transparent p-4 hover:bg-gray-100 dark:hover:border-dark-400 dark:hover:bg-dark-575 md:flex lg:w-1/2"
                >
                    <div class="size-6 text-gray-400 mt-1 me-4">
                        {!! $utility->icon() !!}
                    </div>
                    <div class="mb-4 flex-1 md:mb-0 md:me-6">
                        <ui-heading size="lg">{{ $utility->title() }}</ui-heading>
                        <ui-subheading>{{ $utility->description() }}</ui-subheading>
                    </div>
                </a>
            @endforeach
        </div>
    </ui-card-panel>

    <x-statamic::docs-callout
        topic="{{ __('Utilities') }}"
        url="{{ Statamic::docsUrl('extending/utilities') }}"
    />
@endsection
