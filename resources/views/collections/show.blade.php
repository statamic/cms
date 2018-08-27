@extends('statamic::layout')

@section('content')
    <div class="flex mb-3">
        <h1 class="flex-1">
            {{ __('Collections') }}
            <svg width="24" height="24" viewBox="0 0 24 24" class="align-middle"><path d="M10.414 7.05l4.95 4.95-4.95 4.95L9 15.534 12.536 12 9 8.464z" fill="currentColor" fill-rule="evenodd"></path></svg>
            {{ $collection->title() }}
        </h1>
        <a href="{{ cp_route('collections.entries.create', $collection->path()) }}" class="btn btn-primary">{{ __('Create Entry') }}</a>
    </div>

    <entry-list
        :columns="['title', 'last_modified', 'order', 'published', 'slug']"
        :visible-columns="['title', 'slug']"
        :initial-rows="{{ $entries }}"
        :endpoints="{ bulkDelete: '/bulk/delete/route' }">
    </entry-list>

@endsection
