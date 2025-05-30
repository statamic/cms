@php use function Statamic\trans as __; @endphp

@extends('statamic::layout')
@section('title', __('Blueprints'))

@section('content')
    <ui-header title="{{ __('Blueprints') }}" icon="blueprints">
        <ui-dropdown>
            <template #trigger>
                <ui-button
                    text="{{ __('Create Blueprint') }}"
                    icon-append="ui/chevron-down"
                    variant="primary"
                ></ui-button>
            </template>

            <ui-dropdown-menu>
                @foreach (Statamic\Facades\Collection::all() as $collection)
                    @if ($loop->first)
                        <ui-dropdown-label>
                            {{ __('Collections') }}
                        </ui-dropdown-label>
                    @endif
                    <ui-dropdown-item
                        href="{{ cp_route('collections.blueprints.create', $collection) }}"
                        icon="collections"
                        text="{{ __($collection->title()) }}"
                    ></ui-dropdown-item>
                @endforeach

                @foreach (Statamic\Facades\Taxonomy::all() as $taxonomy)
                    @if ($loop->first)
                        <ui-dropdown-label>
                            {{ __('Taxonomies') }}
                        </ui-dropdown-label>
                    @endif
                    <ui-dropdown-item
                        href="{{ cp_route('taxonomies.blueprints.create', $taxonomy) }}"
                        icon="taxonomies"
                        text="{{ __($taxonomy->title()) }}"
                    ></ui-dropdown-item>
                @endforeach
            </ui-dropdown-menu>
        </ui-dropdown>
    </ui-header>

    @if (Statamic\Facades\Collection::all()->count() > 0)
        <ui-subheading size="xl" class="mb-2">{{ __('Collections') }}</ui-subheading>
            <ui-panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Blueprint') }}</th>
                            <th>{{ __('Collection') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach (Statamic\Facades\Collection::all() as $collection)
                        @foreach ($collection->entryBlueprints() as $blueprint)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <ui-status-indicator status="{{ $blueprint->hidden() ? 'hidden' : 'published' }}" v-tooltip="'{{ __($blueprint->hidden() ? 'Hidden': 'Visible') }}'"></ui-status-indicator>
                                        <a href="{{ cp_route('collections.blueprints.edit', [$collection, $blueprint]) }}" v-pre>{{ __($blueprint->title()) }}</a>
                                    </div>
                                </td>
                                <td class="text-end" v-pre>
                                    <span class="pe-2 font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ __($collection->title()) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </ui-panel>
    @endif

    @foreach (Statamic\Facades\Taxonomy::all() as $taxonomy)
        @if ($loop->first)
        <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Taxonomies') }}</h3>
        <div class="card p-0 mb-4">
            <table class="data-table">
        @endif
                @foreach ($taxonomy->termBlueprints() as $blueprint)
                    <tr>
                        <td>
                            <div class="flex items-center">
                                <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/tags')</div>
                                <span class="little-dot {{ $blueprint->hidden() ? 'hollow' : 'bg-green-600' }} rtl:ml-2 ltr:mr-2" v-tooltip="'{{ __($blueprint->hidden() ? 'Hidden': 'Visible') }}'"></span>
                                <a href="{{ cp_route('taxonomies.blueprints.edit', [$taxonomy, $blueprint]) }}" v-pre>{{ __($blueprint->title()) }}</a>
                            </div>
                        </td>
                        <td class="rtl:text-left ltr:text-right text-2xs" v-pre>{{ __($taxonomy->title()) }}</td>
                    </tr>
                @endforeach
        @if ($loop->last)
            </table>
        </div>
        @endif
    @endforeach

    @foreach (Statamic\Facades\Nav::all() as $nav)
        @if ($loop->first)
        <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Navigation') }}</h3>
        <div class="card p-0 mb-4">
            <table class="data-table">
        @endif
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/hierarchy-files')</div>
                            <a href="{{ cp_route('navigation.blueprint.edit', $nav->handle()) }}" v-pre>{{ __($nav->title()) }}</a>
                        </div>
                    </td>
                </tr>
        @if ($loop->last)
            </table>
        </div>
        @endif
    @endforeach

    @foreach (Statamic\Facades\GlobalSet::all() as $set)
        @if ($loop->first)
        <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Globals') }}</h3>
        <div class="card p-0 mb-4">
            <table class="data-table">
        @endif
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/earth')</div>
                            <a href="{{ cp_route('globals.blueprint.edit', $set->handle()) }}" v-pre>{{ __($set->title()) }}</a>
                        </div>
                    </td>
                </tr>
        @if ($loop->last)
            </table>
        </div>
        @endif
    @endforeach

    @foreach (Statamic\Facades\AssetContainer::all() as $container)
        @if ($loop->first)
        <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Asset Containers') }}</h3>
        <div class="card p-0 mb-4">
            <table class="data-table">
        @endif
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/assets')</div>
                            <a href="{{ cp_route('asset-containers.blueprint.edit', $container->handle()) }}" v-pre>{{ __($container->title()) }}</a>
                        </div>
                    </td>
                </tr>
        @if ($loop->last)
            </table>
        </div>
        @endif
    @endforeach

    @if (Statamic\Facades\User::current()->can('configure form fields'))
        @foreach (Statamic\Facades\Form::all() as $form)
            @if ($loop->first)
        <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Forms') }}</h3>
        <div class="card p-0 mb-2">
            <table class="data-table">
            @endif
                <tr>
                    <td>
                        <div class="flex items-center">
                            <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/drawer-file')</div>
                            <a href="{{ cp_route('forms.blueprint.edit', $form->handle()) }}" v-pre>{{ __($form->title()) }}</a>
                        </div>
                    </td>
            @if ($loop->last)
                </tr>
        </table>
            @endif
    </div>
        @endforeach
    @endif

    <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ __('Users') }}</h3>
    <div class="card p-0 mb-4">
        <table class="data-table">
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/users')</div>
                        <a href="{{ cp_route('users.blueprint.edit') }}">{{ __('User') }}</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/user_groups')</div>
                        <a href="{{ cp_route('user-groups.blueprint.edit') }}">{{ __('Group') }}</a>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @foreach ($additional as $namespace)
    <h3 class="little-heading rtl:pr-0 ltr:pl-0 mb-2">{{ $namespace['title'] }}</h3>
    <div class="card p-0 mb-4">
        <table class="data-table">
        @foreach ($namespace['blueprints'] as $blueprint)
            <tr>
                <td>
                    <div class="flex items-center">
                        <div class="w-4 h-4 rtl:ml-4 ltr:mr-4">@cp_svg('icons/light/blueprint')</div>
                        <a href="{{ cp_route('blueprints.edit', [$blueprint['namespace'], $blueprint['handle']]) }}">{{ $blueprint['title'] }}</a>
                    </div>
                </td>
                <th class="actions-column">
                    @if ($blueprint['is_resettable'])
                        <dropdown-list class="dropdown-list">
                            <ui-dropdown-item :text="__('Reset')" class="warning" @click="$refs[`resetter_{{ $blueprint['namespace'] }}_{{ $blueprint['handle'] }}`].confirm()">
                                <blueprint-resetter
                                    ref="resetter_{{ $blueprint['namespace'] }}_{{ $blueprint['handle'] }}"
                                    :resource="{{ Js::from($blueprint) }}"
                                    reload
                                >
                                </blueprint-resetter>
                            </ui-dropdown-item>
                        </dropdown-list>
                    @endif
                </td>
            </tr>
        @endforeach
        </table>
    </div>
    @endforeach

    @include('statamic::partials.docs-callout', [
        'topic' => __('Blueprints'),
        'url' => Statamic::docsUrl('blueprints')
    ])

@endsection
