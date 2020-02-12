@inject('str', 'Statamic\Support\Str')
@extends('statamic::layout')
@section('title', Statamic::crumb($structure->title(), 'Structures'))

@section('content')

    <page-tree
        title="{{ $structure->title() }}"
        breadcrumb-url="{{ cp_route('structures.index') }}"
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
        :collection-blueprints="{{ $collectionBlueprints->toJson() }}"
    >
        <template slot="header">
            <div>
                @include('statamic::partials.breadcrumb', [
                    'url' => cp_route('structures.index'),
                    'title' => __('Structures')
                ])
                <h1 class="flex-1">
                    {{ $structure->title() }}
                </h1>
            </div>
        </template>

        <template slot="no-pages-svg">
            @svg('empty/structure')
        </template>
    </page-tree>

@endsection
