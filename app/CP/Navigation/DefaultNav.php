<?php

namespace Statamic\CP\Navigation;

use Statamic\API\Nav;
use Statamic\API\Site;
use Statamic\API\Form as FormAPI;
use Statamic\API\Role as RoleAPI;
use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Forms\Form;
use Statamic\API\GlobalSet as GlobalSetAPI;
use Statamic\API\Structure as StructureAPI;
use Statamic\API\UserGroup as UserGroupAPI;
use Statamic\API\Collection as CollectionAPI;
use Statamic\Contracts\Data\Globals\GlobalSet;
use Statamic\Contracts\Data\Entries\Collection;
use Statamic\Contracts\Data\Structures\Structure;
use Statamic\API\AssetContainer as AssetContainerAPI;

class DefaultNav
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

        Nav::topLevel('Playground')
            ->route('playground')
            ->icon('playground');

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
                return StructureAPI::all()->map(function ($structure) {
                    return Nav::item($structure->title())
                              ->url($structure->showUrl())
                              ->can('view', $structure);
                });
            });

        Nav::content('Taxonomies')
            ->route('taxonomies.index')
            ->icon('tags');
            // ->can() // TODO: Permission to manage taxonomies?

        Nav::content('Assets')
            ->route('assets.index')
            ->icon('assets')
            // ->can() // TODO: Permission to manage assets/containers?
            ->children(function () {
                return AssetContainerAPI::all()->map(function ($assetContainer) {
                    return Nav::item($assetContainer->title())
                        ->url($assetContainer->showUrl());
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
                        ->url($form->url())
                        ->can('view', $form);
                });
            });

        Nav::tools('Updates')
            ->route('updater.index')
            ->icon('loading-bar')
            ->view('statamic::nav.updates')
            ->can('view updates');

        Nav::tools('Utilities')
            ->route('utilities.phpinfo')
            ->active('utilities*')
            ->icon('settings-slider')
            // ->can() // TODO: Permission to use utilities?
            ->children([
                Nav::item('Cache')->route('utilities.cache.index'),
                Nav::item('PHP Info')->route('utilities.phpinfo'),
                Nav::item('Search')->route('utilities.search'),
            ]);

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
            ->can('index', User::class);

        Nav::users('Groups')
            ->route('user-groups.index')
            ->icon('users-multiple')
            // ->can() // TODO: Permission to manage groups?
            ->children(function () {
                return UserGroupAPI::all()->map(function ($userGroup) {
                    return Nav::item($userGroup->title())
                              ->url($userGroup->editUrl());
                });
            });

        Nav::users('Permissions')
            ->route('roles.index')
            ->icon('shield-key')
            // ->can() // TODO: Permission to manage permissions?
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
        Nav::site('Addons')
            ->route('addons.index')
            ->icon('addons');

        Nav::site('Preferences')
            ->route('')
            ->icon('hammer-wrench');

        Nav::site('Blueprints')
            ->route('blueprints.index')
            ->icon('blueprints');

        Nav::site('Fieldsets')
            ->route('fieldsets.index')
            ->icon('wireframe');

        return $this;
    }
}
