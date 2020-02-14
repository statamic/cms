@extends('statamic::layout')
@section('title', __('Utilities'))

@section('content')

    <header class="mb-3">
        <h1>{{ __('Utilities') }}</h1>
    </header>

    <div class="card p-0 content">
        <div class="flex flex-wrap">
        @foreach ($utilities as $utility)
            <a href="{{ $utility->url() }}" class="w-full lg:w-1/2 p-3 border-t md:flex items-start hover:bg-grey-10 group {{ $loop->odd ? 'lg:border-r' : '' }}">
                <div class="h-8 w-8 mr-2 hidden md:block text-grey-80">
                    @svg($utility->icon())
                </div>
                <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                    <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ $utility->title() }} &rarr;</h3>
                    <p>{{ $utility->description() }}</p>
                </div>
            </a>
        @endforeach
        </div>
    </div>



@endsection
