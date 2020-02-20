<?php

namespace Statamic\CP\Navigation;

use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\User;
use Statamic\Facades\Form as FormAPI;
use Statamic\Facades\Role as RoleAPI;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Contracts\Forms\Form;
use Statamic\Facades\Taxonomy as TaxonomyAPI;
use Statamic\Facades\GlobalSet as GlobalSetAPI;
use Statamic\Facades\Structure as StructureAPI;
use Statamic\Facades\UserGroup as UserGroupAPI;
use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Contracts\Entries\Collection;
use Statamic\Contracts\Taxonomies\Taxonomy;
use Statamic\Contracts\Structures\Structure;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerAPI;
use Statamic\Facades\Utility;

class CoreNav
{
    const ALLOWED_TOP_LEVEL = [
        'Dashboard',
        'Playground',
    ];

    /**
     * Make default nav items.
     */
    public static function make()
    {
        (new static)
            ->makeTopLevel()
            ->makeContentSection()
            ->makeToolsSection()
            ->makeUsersSection()
            ->makeSiteSection();
    }

    /**
     * Make top level items.
     *
     * @return $this
     */
    protected function makeTopLevel()
    {
        Nav::topLevel('Dashboard')
            ->route('dashboard')
            ->icon('charts');

        // Nav::topLevel('Playground')
        //     ->route('playground')
        //     ->icon('playground');

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
                return CollectionAPI::all()->map(function ($collection) {
                    return Nav::item($collection->title())
                              ->url($collection->showUrl())
                              ->can('view', $collection);
                });
            });

        Nav::content('Structures')
            ->route('structures.index')
            ->icon('hierarchy-files')
            ->can('index', Structure::class)
            ->children(function () {
                return StructureAPI::all()->reject(function ($structure) {
                    return $structure->isCollectionBased();
                })->map(function ($structure) {
                    return Nav::item($structure->title())
                              ->url($structure->showUrl())
                              ->can('view', $structure);
                });
            });

        Nav::content('Taxonomies')
            ->route('taxonomies.index')
            ->icon('tags')
            ->can('index', Taxonomy::class)
            ->children(function () {
                return TaxonomyAPI::all()->map(function ($taxonomy) {
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
                return AssetContainerAPI::all()->map(function ($assetContainer) {
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
                return GlobalSetAPI::all()->map(function ($globalSet) {
                    $globalSet = $globalSet->in(Site::selected()->handle());
                    return Nav::item($globalSet->title())
                              ->url($globalSet->editUrl())
                              ->can('view', $globalSet);
                });
            });

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
                return FormAPI::all()->map(function ($form) {
                    return Nav::item($form->title())
                        ->url($form->showUrl())
                        ->can('view', $form);
                });
            });

        // Nav::tools('Updates')
        //     ->route('updater')
        //     ->icon('loading-bar')
        //     ->view('statamic::nav.updates')
        //     ->can('view updates');

        $this->makeUtilitiesSection();

        return $this;
    }

    protected function makeUtilitiesSection()
    {
        $utilities = Utility::authorized()->map(function ($utility) {
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
        Nav::users('Users')
            ->route('users.index')
            ->icon('users-box')
            ->can('index', UserContract::class);

        Nav::users('Groups')
            ->route('user-groups.index')
            ->icon('users-multiple')
            ->can('edit user groups')
            ->children(function () {
                return UserGroupAPI::all()->map(function ($userGroup) {
                    return Nav::item($userGroup->title())
                              ->url($userGroup->editUrl());
                });
            });

        Nav::users('Permissions')
            ->route('roles.index')
            ->icon('shield-key')
            ->can('edit roles')
            ->children(function () {
                return RoleAPI::all()->map(function ($role) {
                    return Nav::item($role->title())
                        ->url($role->editUrl());
                });
            });

        return $this;
    }

    /**
     * Make site section items.
     *
     * @return $this
     */
    protected function makeSiteSection()
    {
        // Nav::site('Addons')
        //     ->route('addons.index')
        //     ->icon('addons')
        //     ->can('configure addons');

        Nav::site('Fields')
            ->route('fields.index')
            ->icon('wireframe')
            ->can('configure fields')
            ->children([
                Nav::item('Blueprints')->route('blueprints.index'),
                Nav::item('Fieldsets')->route('fieldsets.index'),
            ]);

        // Nav::site('Preferences')
        //     ->route('')
        //     ->icon('hammer-wrench');

        return $this;
    }
}
