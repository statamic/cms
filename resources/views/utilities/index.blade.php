@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Utilities'))

@section('content')

    <header class="mb-6">
        <h1>{{ __('Utilities') }}</h1>
    </header>

    <div class="card p-4 content">
        <div class="flex flex-wrap">
        @foreach ($utilities as $utility)
            <a href="{{ $utility->url() }}" class="w-full lg:w-1/2 p-4 md:flex items-start hover:bg-gray-200 dark:hover:bg-dark-575 border border-transparent dark:hover:border-dark-400 rounded-md group">
                <div class="h-8 w-8 rtl:ml-4 ltr:mr-4 text-gray-800 dark:text-dark-175">
                    {!! $utility->icon() !!}
                </div>
                <div class="text-blue flex-1 mb-4 md:mb-0 rtl:md:ml-6 ltr:md:mr-6">
                    <h3>{{ $utility->title() }}</h3>
                    <p class="text-xs">{{ $utility->description() }}</p>
                </div>
            </a>
        @endforeach
        </div>
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Utilities'),
        'url' => Statamic::docsUrl('extending/utilities')
    ])

@endsection
