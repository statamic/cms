@extends('statamic::layout')

@section('content')

    <page-tree
        pages-url="{{ cp_route('structures.pages.index', $structure->handle()) }}"
        submit-url="{{ cp_route('structures.pages.store', $structure->handle()) }}"
        sound-drop-url="{{ cp_resource_url('audio/click.mp3') }}"
    >
        <template slot="header">
            <h1 class="flex-1">
                <a href="{{ cp_route('structures.index')}}">{{ __('Structures') }}</a>
                @svg('chevron-right')
                {{ $structure->title() }}
            </h1>
        </template>
    </page-tree>

@endsection
