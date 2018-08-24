@extends('statamic::layout')

@section('content')
    <div class="flex mb-3">
        <h1 class="flex-1">{{ $collection->title() }}</h1>
        <a href="{{ cp_route('collections.entries.create', $collection->path()) }}" class="btn btn-primary">{{ __('Create Entry') }}</a>
    </div>

    <entry-list
        :columns="['title', 'last_modified', 'order', 'published', 'slug']"
        :visible-columns="['title', 'slug']"
        :initial-rows="{{ $entries }}"
        :endpoints="{ bulkDelete: '/bulk/delete/route' }">
    </entry-list>

@endsection
