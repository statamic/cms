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
                        href="{{ cp_route('blueprints.collections.create', $collection) }}"
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
                        href="{{ cp_route('blueprints.taxonomies.create', $taxonomy) }}"
                        icon="taxonomies"
                        text="{{ __($taxonomy->title()) }}"
                    ></ui-dropdown-item>
                @endforeach
            </ui-dropdown-menu>
        </ui-dropdown>
    </ui-header>

    <section class="space-y-6">
    @if (Statamic\Facades\Collection::all()->count() > 0)
        <ui-subheading size="lg" class="mb-2">{{ __('Collections') }}</ui-subheading>
            <ui-panel>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('Blueprint') }}</th>
                            <th class="text-end!" scope="col">{{ __('Collection') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach (Statamic\Facades\Collection::all() as $collection)
                        @foreach ($collection->entryBlueprints() as $blueprint)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <ui-icon name="collections" class="text-gray-500 me-1" />
                                        <ui-status-indicator status="{{ $blueprint->hidden() ? 'hidden' : 'published' }}" v-tooltip="'{{ __($blueprint->hidden() ? 'Hidden': 'Visible') }}'"></ui-status-indicator>
                                        <a href="{{ cp_route('blueprints.collections.edit', [$collection, $blueprint]) }}" v-pre>{{ __($blueprint->title()) }}</a>
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

    @if (Statamic\Facades\Taxonomy::all()->count() > 0)
        <ui-subheading size="lg" class="mb-2">{{ __('Taxonomies') }}</ui-subheading>
        <ui-panel>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>{{ __('Blueprint') }}</th>
                        <th class="text-end!">{{ __('Taxonomy') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (Statamic\Facades\Taxonomy::all() as $taxonomy)
                        @foreach ($taxonomy->termBlueprints() as $blueprint)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-2">
                                        <ui-icon name="taxonomies" class="text-gray-500 me-1" />
                                        <ui-status-indicator status="{{ $blueprint->hidden() ? 'hidden' : 'published' }}" v-tooltip="'{{ __($blueprint->hidden() ? 'Hidden': 'Visible') }}'"></ui-status-indicator>
                                        <a href="{{ cp_route('blueprints.taxonomies.edit', [$taxonomy, $blueprint]) }}" v-pre>{{ __($blueprint->title()) }}</a>
                                    </div>
                                </td>
                                <td class="text-end" v-pre>
                                    <span class="pe-2 font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ __($taxonomy->title()) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </ui-panel>
    @endif

    @if (Statamic\Facades\Nav::all()->count() > 0)
        <ui-subheading size="lg" class="mb-2">{{ __('Navigation') }}</ui-subheading>
        <ui-panel>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-start!">{{ __('Blueprint') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (Statamic\Facades\Nav::all() as $nav)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <ui-icon name="navigation" class="text-gray-500 me-1" />
                                    <a href="{{ cp_route('blueprints.navigation.edit', $nav->handle()) }}" v-pre>{{ __($nav->title()) }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </ui-panel>
    @endif

    @if (Statamic\Facades\AssetContainer::all()->count() > 0)
        <ui-subheading size="lg" class="mb-2">{{ __('Asset Containers') }}</ui-subheading>
        <ui-panel>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-start!">{{ __('Blueprint') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (Statamic\Facades\AssetContainer::all() as $container)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <ui-icon name="assets" class="text-gray-500 me-1" />
                                    <a href="{{ cp_route('blueprints.asset-containers.edit', $container->handle()) }}" v-pre>{{ __($container->title()) }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </ui-panel>
    @endif

    @if (Statamic\Facades\GlobalSet::all()->count() > 0)
        <ui-subheading size="lg" class="mb-2">{{ __('Globals') }}</ui-subheading>
        <ui-panel>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-start!">{{ __('Blueprint') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (Statamic\Facades\GlobalSet::all() as $set)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <ui-icon name="globals" class="text-gray-500 me-1" />
                                    <a href="{{ cp_route('blueprints.globals.edit', $set->handle()) }}" v-pre>{{ __($set->title()) }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </ui-panel>
    @endif

    @if (Statamic\Facades\User::current()->can('configure form fields') && Statamic\Facades\Form::all()->count() > 0)
        <ui-subheading size="lg" class="mb-2">{{ __('Forms') }}</ui-subheading>
        <ui-panel>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-start!">{{ __('Blueprint') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (Statamic\Facades\Form::all() as $form)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <ui-icon name="forms" class="text-gray-500 me-1" />
                                    <a href="{{ cp_route('blueprints.forms.edit', $form->handle()) }}" v-pre>{{ __($form->title()) }}</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </ui-panel>
    @endif

    <ui-subheading size="lg" class="mb-2">{{ __('Users') }}</ui-subheading>
    <ui-panel>
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-start!">{{ __('Blueprint') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <ui-icon name="users" class="text-gray-500 me-1" />
                            <a href="{{ cp_route('blueprints.users.edit') }}">{{ __('User') }}</a>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <ui-icon name="groups" class="text-gray-500 me-1" />
                            <a href="{{ cp_route('blueprints.user-groups.edit') }}">{{ __('Group') }}</a>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </ui-panel>

    @foreach ($additional as $namespace)
        <ui-subheading size="lg" class="mb-2">{{ $namespace['title'] }}</ui-subheading>
        <ui-panel>
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="text-start!">{{ __('Blueprint') }}</th>
                        <th scope="col" class="actions-column"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($namespace['blueprints'] as $blueprint)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ cp_route('blueprints.edit', [$blueprint['namespace'], $blueprint['handle']]) }}">{{ $blueprint['title'] }}</a>
                                </div>
                            </td>
                            <td class="actions-column">
                                @if ($blueprint['is_resettable'])
                                    <ui-dropdown>
                                        <ui-dropdown-menu>
                                            <ui-dropdown-item :text="__('Reset')" variant="destructive" @click="$refs[`resetter_{{ $blueprint['namespace'] }}_{{ $blueprint['handle'] }}`].confirm()">
                                            </ui-dropdown-item>
                                        </ui-dropdown-menu>
                                    </ui-dropdown>

                                    <blueprint-resetter
                                        ref="resetter_{{ $blueprint['namespace'] }}_{{ $blueprint['handle'] }}"
                                        :resource="{{ Js::from($blueprint) }}"
                                        reload
                                    ></blueprint-resetter>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </ui-panel>
    @endforeach
    </section>

    <x-statamic::docs-callout
        :topic="__('Blueprints')"
        :url="Statamic::docsUrl('blueprints')"
    />
@endsection
