<?php

namespace Statamic\CP\Navigation;

use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Forms\Form;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Structures\Nav as NavContract;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Facades\AssetContainer as AssetContainerAPI;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\Form as FormAPI;
use Statamic\Facades\GlobalSet as GlobalSetAPI;
use Statamic\Facades\Nav as NavAPI;
use Statamic\Facades\Role as RoleAPI;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy as TaxonomyAPI;
use Statamic\Facades\User;
use Statamic\Facades\UserGroup as UserGroupAPI;
use Statamic\Facades\Utility;
use Statamic\Statamic;

class CoreNav
{
    /**
     * Make default nav items.
     */
    public static function make()
    {
        (new static)
            ->makeTopLevel()
            ->makeContentSection()
            ->makeFieldsSection()
            ->makeToolsSection()
            ->makeUsersSection();
    }

    /**
     * Make top level items.
     *
     * @return $this
     */
    protected function makeTopLevel()
    {
        if (count(config('statamic.cp.widgets')) > 0 || config('statamic.cp.start_page') === 'dashboard') {
            Nav::topLevel('Dashboard')
                ->route('dashboard')
                ->icon('charts');
        }

        return $this;
    }

    /**
     * Make content section items.
     *
     * @return $this
     */
    protected function makeContentSection()
    {
        Nav::content('Collections')
            ->route('collections.index')
            ->icon('content-writing')
            ->can('index', Collection::class)
            ->children(function () {
                return CollectionAPI::all()->sortBy->title()->map(function ($collection) {
                    return Nav::item($collection->title())
                        ->url($collection->showUrl())
                        ->can('view', $collection);
                });
            });

        Nav::content('Navigation')
            ->route('navigation.index')
            ->icon('hierarchy-files')
            ->can('index', NavContract::class)
            ->children(function () {
                return NavAPI::all()->sortBy->title()->map(function ($nav) {
                    return Nav::item($nav->title())
                        ->url($nav->showUrl())
                        ->can('view', $nav);
                });
            });

        Nav::content('Taxonomies')
            ->route('taxonomies.index')
            ->icon('tags')
            ->can('index', Taxonomy::class)
            ->children(function () {
                return TaxonomyAPI::all()->sortBy->title()->map(function ($taxonomy) {
                    return Nav::item($taxonomy->title())
                        ->url($taxonomy->showUrl())
                        ->can('view', $taxonomy);
                });
            });

        Nav::content('Assets')
            ->route('assets.index')
            ->icon('assets')
            ->can('index', AssetContainer::class)
            ->children(function () {
                return AssetContainerAPI::all()->sortBy->title()->map(function ($assetContainer) {
                    return Nav::item($assetContainer->title())
                        ->url($assetContainer->showUrl())
                        ->can('view', $assetContainer);
                });
            });

        Nav::content('Globals')
            ->route('globals.index')
            ->icon('earth')
            ->can('index', GlobalSet::class)
            ->children(function () {
                return GlobalSetAPI::all()->sortBy->title()->map(function ($globalSet) {
                    $localized = $globalSet->inSelectedSite();

                    if (! $localized && User::current()->cant('edit', $globalSet)) {
                        return null;
                    }

                    return Nav::item($globalSet->title())
                        ->url($localized ? $localized->editUrl() : $globalSet->editUrl())
                        ->can('view', $globalSet);
                })->filter();
            });

        return $this;
    }

    /**
     * Make fields section items.
     *
     * @return $this
     */
    protected function makeFieldsSection()
    {
        Nav::fields('Blueprints')
            ->route('blueprints.index')
            ->icon('blueprint')
            ->can('configure fields');

        Nav::fields('Fieldsets')
            ->route('fieldsets.index')
            ->icon('fieldsets')
            ->can('configure fields');

        return $this;
    }

    /**
     * Make tools section items.
     *
     * @return $this
     */
    protected function makeToolsSection()
    {
        Nav::tools('Forms')
            ->route('forms.index')
            ->icon('drawer-file')
            ->can('index', Form::class)
            ->children(function () {
                return FormAPI::all()->sortBy->title()->map(function ($form) {
                    return Nav::item($form->title())
                        ->url($form->showUrl())
                        ->can('view', $form);
                });
            });

        Nav::tools('Updates')
            ->route('updater')
            ->icon('loading-bar')
            ->view('statamic::nav.updates')
            ->can('view updates');

        Nav::tools('Addons')
            ->route('addons.index')
            ->icon('addons')
            ->can('configure addons');

        if (Stache::duplicates()->isNotEmpty()) {
            Nav::tools('Duplicate IDs')
                ->route('duplicates')
                ->icon('duplicate-ids')
                ->view('statamic::nav.duplicates')
                ->can('resolve duplicate ids');
        }

        $this->makeUtilitiesSection();

        if (config('statamic.graphql.enabled') && Statamic::pro()) {
            Nav::tools('GraphQL')
                ->route('graphql.index')
                ->icon('array')
                ->attributes(['target' => '_blank'])
                ->can('view graphql');
        }

        return $this;
    }

    protected function makeUtilitiesSection()
    {
        $utilities = Utility::authorized()->sortBy->navTitle()->map(function ($utility) {
            return Nav::item($utility->navTitle())->url($utility->url());
        });

        if (count($utilities)) {
            Nav::tools('Utilities')
                ->route('utilities.index')
                ->icon('settings-slider')
                ->children($utilities);
        }

        return $this;
    }

    /**
     * Make users section items.
     *
     * @return $this
     */
    protected function makeUsersSection()
    {
        if (! Statamic::pro()) {
            return $this;
        }

        Nav::users('Users')
            ->route('users.index')
            ->icon('users-box')
            ->can('index', UserContract::class);

        Nav::users('Groups')
            ->route('user-groups.index')
            ->icon('users-multiple')
            ->can('edit user groups')
            ->children(function () {
                return UserGroupAPI::all()->sortBy->title()->map(function ($userGroup) {
                    return Nav::item($userGroup->title())
                        ->url($userGroup->showUrl());
                });
            });

        Nav::users('Permissions')
            ->route('roles.index')
            ->icon('shield-key')
            ->can('edit roles')
            ->children(function () {
                return RoleAPI::all()->sortBy->title()->map(function ($role) {
                    return Nav::item($role->title())
                        ->url($role->editUrl());
                });
            });

        return $this;
    }
}
