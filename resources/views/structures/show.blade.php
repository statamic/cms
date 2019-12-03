@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($structure->title(), 'Structures'))

@section('content')

    <page-tree
        :initial-pages="{{ json_encode($pages) }}"
        pages-url="{{ cp_route('structures.pages.index', $structure->handle()) }}"
        submit-url="{{ cp_route('structures.pages.store', $structure->handle()) }}"
        edit-url="{{ cp_route('structures.edit', $structure->handle()) }}"
        create-url="{{ $hasCollection ? cp_route('collections.entries.create', [$structure->collection()->handle(), $site]) : null }}"
        sound-drop-url="{{ Statamic::cpAssetUrl('audio/click.mp3') }}"
        site="{{ $site }}"
        :localizations="{{ json_encode($localizations) }}"
        :collections="{{ json_encode($collections) }}"
        :max-depth="{{ $structure->maxDepth() ?? 'Infinity' }}"
        :expects-root="{{ $str::bool($expectsRoot) }}"
        :has-collection="{{ $str::bool($hasCollection) }}"
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
