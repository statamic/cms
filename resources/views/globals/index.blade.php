@extends('statamic::layout')

@section('content')

    @if(count($globals) == 0)
        <div class="text-center max-w-md mx-auto mt-5 screen-centered border-2 border-dashed rounded-lg px-4 py-8">
            @svg('empty/global')
            <h1 class="my-3">{{ __('Create your first Global Set now') }}</h1>
            <p class="text-grey mb-3">
                {{ __('Global Sets contain content available across the entire site, like company details, contact information, or front-end settings.') }}
            </p>
            @can('super')
                <a href="{{ cp_route('forms.create') }}" class="btn-primary btn-lg">{{ __('Create Global Set') }}</a>
            @endcan
        </div>
    @endif

    @if(count($globals) > 0)
        <div class="flex items-center mb-3">
            <h1 class="flex-1">{{ $title }}</h1>
            @can('super')
                <a href="{{ cp_route('forms.create') }}" class="btn btn-primary">{{ __('Create Global Set') }}</a>
            @endcan
        </div>

        <global-listing :globals="{{ json_encode($globals) }}"></global-listing>
    @endif

@endsection
