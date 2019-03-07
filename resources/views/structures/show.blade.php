@extends('statamic::layout')

@section('content')

    <page-tree
        :initial-pages="{{ json_encode($pages) }}"
        pages-url="{{ cp_route('structures.pages.index', $structure->handle()) }}"
        submit-url="{{ cp_route('structures.pages.store', $structure->handle()) }}"
        edit-url="{{ cp_route('structures.edit', $structure->handle()) }}"
        sound-drop-url="{{ Statamic::assetUrl('audio/click.mp3') }}"
        :root="{{ json_encode($root) }}"
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
