@extends('statamic::layout')
@section('title', __('Utilities'))

@section('content')

    <div class="content">
        <h1 class="mb">Utilities</h1>
    </div>

    <div class="flex flex-wrap -mx-2 mt-3">
        @foreach ($utilities as $utility)
            <div class="w-full md:w-1/3 px-2 mb-4">
                <div class="card p-0 content border-t-6 border-blue">
                    <div class="p-2">
                        <h2><a href="{{ $utility->url() }}" class="text-grey-90 hover:text-blue">{{ $utility->title() }}</a></h2>
                        <p>{{ $utility->description() }}</p>
                        @if ($utility->docsUrl())
                            <p><a href="{{ $utility->docsUrl() }}" class="font-bold text-blue">{{ __('Read the Docs') }}</a><span class="inline-block text-blue w-4 h-4 ml-1">@svg('external-link')</span></p>
                        @endif
                    </div>
                    <div class="flex p-2 border-t items-center">
                        <a href="{{ $utility->url() }}" class="font-bold text-blue text-sm hover:text-grey-90">{{ $utility->title() }} &rarr;</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@endsection
