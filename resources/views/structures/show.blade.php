@extends('statamic::layout')

@section('content')

    <page-tree
        :initial-pages="{{ json_encode($pages) }}"
        pages-url="{{ cp_route('structures.pages.index', $structure->handle()) }}"
        submit-url="{{ cp_route('structures.pages.store', $structure->handle()) }}"
        edit-url="{{ cp_route('structures.edit', $structure->handle()) }}"
        sound-drop-url="{{ Statamic::assetUrl('audio/click.mp3') }}"
        :root="{{ json_encode($root) }}"
        site="{{ $site }}"
        :localizations="{{ json_encode($localizations) }}"
        :collections="{{ json_encode($collections) }}"
        :max-depth="{{ $structure->maxDepth() ?? 'Infinity' }}"
    >
        <template slot="header">
            <h1 class="flex-1">
                <small class="subhead block">
                    <a href="{{ cp_route('structures.index')}}">{{ __('Structures') }}</a>
                </small>
                {{ $structure->title() }}
            </h1>
        </template>

        <template slot="no-pages-svg">
            @svg('empty/structure')
        </template>
    </page-tree>

@endsection
