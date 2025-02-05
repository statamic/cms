@php
    use function Statamic\trans as __;
@endphp

@extends('statamic::layout')
@section('title', __('Utilities'))

@section('content')
    <header class="mb-6">
        <h1>{{ __('Utilities') }}</h1>
    </header>

    <div class="card content p-4">
        <div class="flex flex-wrap">
            @foreach ($utilities as $utility)
                <a
                    href="{{ $utility->url() }}"
                    class="group w-full items-start rounded-md border border-transparent p-4 hover:bg-gray-200 dark:hover:border-dark-400 dark:hover:bg-dark-575 md:flex lg:w-1/2"
                >
                    <div class="h-8 w-8 text-gray-800 dark:text-dark-175 ltr:mr-4 rtl:ml-4">
                        {!! $utility->icon() !!}
                    </div>
                    <div class="mb-4 flex-1 text-blue md:mb-0 ltr:md:mr-6 rtl:md:ml-6">
                        <h3>{{ $utility->title() }}</h3>
                        <p class="text-xs">{{ $utility->description() }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    @include(
        'statamic::partials.docs-callout',
        [
            'topic' => __('Utilities'),
            'url' => Statamic::docsUrl('extending/utilities'),
        ]
    )
@endsection
