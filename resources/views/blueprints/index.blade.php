@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')

    <div class="flex justify-between items-center mb-3">
        <h1>@yield('title')</h1>
        <div v-cloak>
        <dropdown-list class="inline-block">
            <template v-slot:trigger>
                <button class="button btn-primary flex items-center pr-2">
                    {{ __('Create Blueprint') }}
                    <svg-icon name="chevron-down-xs" class="w-2 ml-1" />
                </button>
            </template>

            <h6 class="p-1">{{ __('Collections') }}</h6>
            @foreach (Statamic\Facades\Collection::all() as $collection)
                <dropdown-item redirect="{{ cp_route('collections.blueprints.create', $collection) }}">{{ $collection->title() }}</dropdown-item>
            @endforeach

            <h6 class="p-1 mt-2">{{ __('Taxonomies') }}</h6>
            @foreach (Statamic\Facades\Taxonomy::all() as $taxonomy)
                <dropdown-item redirect="{{ cp_route('taxonomies.blueprints.create', $taxonomy) }}">{{ $taxonomy->title() }}</dropdown-item>
            @endforeach
        </dropdown-list>
        </div>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Collections') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            @foreach (Statamic\Facades\Collection::all() as $collection)
                @foreach ($collection->entryBlueprints() as $blueprint)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-4 h-4 mr-2">@svg('content-writing')</div>
                                <a href="{{ cp_route('collections.blueprints.edit', [$collection, $blueprint]) }}">{{ $blueprint->title() }}</a>
                            </div>
                        </td>
                        <td class="text-right text-2xs">{{ $collection->title() }}</td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Taxonomies') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            @foreach (Statamic\Facades\Taxonomy::all() as $taxonomy)
                @foreach ($taxonomy->termBlueprints() as $blueprint)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-4 h-4 mr-2">@svg('tags')</div>
                                <a href="{{ cp_route('taxonomies.blueprints.edit', [$taxonomy, $blueprint]) }}">{{ $blueprint->title() }}</a>
                            </div>
                        </td>
                        <td class="text-right text-2xs">{{ $taxonomy->title() }}</td>
                    </tr>
                @endforeach
            @endforeach
        </table>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Globals') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            @foreach (Statamic\Facades\GlobalSet::all() as $set)
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="w-4 h-4 mr-2">@svg('earth')</div>
                            <a href="{{ cp_route('globals.blueprint.edit', $set->handle()) }}">{{ $set->title() }}</a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Asset Containers') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            @foreach (Statamic\Facades\AssetContainer::all() as $container)
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="w-4 h-4 mr-2">@svg('assets')</div>
                            <a href="{{ cp_route('asset-containers.blueprint.edit', $container->handle()) }}">{{ $container->title() }}</a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Forms') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            @foreach (Statamic\Facades\Form::all() as $form)
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="w-4 h-4 mr-2">@svg('drawer-file')</div>
                            <a href="{{ cp_route('forms.blueprint.edit', $form->handle()) }}">{{ $form->title() }}</a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </table>
    </div>

    <h3 class="little-heading pl-0 mb-1">{{ __('Other') }}</h3>
    <div class="card p-0 mb-2">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 mr-2">@svg('users')</div>
                        <a href="{{ cp_route('users.blueprint.edit') }}">{{ __('User') }}</a>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
