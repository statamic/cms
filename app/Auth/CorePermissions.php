<?php

namespace Statamic\Auth;

use Statamic\API\Form;
use Statamic\API\Taxonomy;
use Statamic\API\GlobalSet;
use Statamic\API\Collection;
use Statamic\API\Permission;
use Statamic\API\AssetContainer;

class CorePermissions
{
    public function boot()
    {
        $this
            ->register('access cp')
            ->register('view drafts on frontend')
            ->registerCollections()
            ->registerGlobals()
            ->registerTaxonomies()
            ->registerAssetContainers()
            ->registerUpdates()
            ->registerUsers()
            ->registerForms();
    }

    protected function register($permission)
    {
        Permission::register($permission);

        return $this;
    }

    protected function registerCollections()
    {
        Permission::register('view {collection} entries', function ($permission) {
            $permission->withChildren([
                Permission::make('edit {collection} entries')->withChildren([
                    Permission::make('create {collection} entries'),
                    Permission::make('delete {collection} entries')
                ])
            ])->withReplacements('collection', function () {
                return Collection::all()->map(function ($collection) {
                    return ['value' => $collection->handle(), 'label' => $collection->title()];
                });
            });
        });

        return $this;
    }

    protected function registerGlobals()
    {
        Permission::register('edit {global} globals', function ($permission) {
            $permission->withReplacements('global', function () {
                return GlobalSet::all()->map(function ($global) {
                    return ['value' => $global->handle(), 'label' => $global->title()];
                });
            });
        });

        return $this;
    }

    protected function registerTaxonomies()
    {
        return $this; // TODO: Remove this when taxonomies work again.

        Permission::register('view {taxonomy} terms', function ($permission) {
            $permission->withChildren([
                Permission::make('edit {taxonomy} terms')->withChildren([
                    Permission::make('create {taxonomy} terms'),
                    Permission::make('delete {taxonomy} terms')
                ])
            ])->withReplacements('taxonomy', function () {
                return Taxonomy::all()->map(function ($taxonomy) {
                    return ['value' => $taxonomy->handle(), 'label' => $taxonomy->title()];
                });
            });
        });

        return $this;
    }

    protected function registerAssetContainers()
    {
        Permission::register('view {container} assets', function ($permission) {
            $permission->withChildren([
                Permission::make('upload {container} assets'),
                Permission::make('edit {container} assets')->withChildren([
                    Permission::make('delete {container} assets')
                ])
            ])->withReplacements('container', function () {
                return AssetContainer::all()->map(function ($container) {
                    return ['value' => $container->handle(), 'label' => $container->title()];
                });
            });
        });

        return $this;
    }

    protected function registerUpdates()
    {
        Permission::register('view updates', function ($permission) {
            $permission->withChildren([
                Permission::make('perform updates'),
            ]);
        });

        return $this;
    }

    protected function registerUsers()
    {
        Permission::register('view users', function ($permission) {
            $permission->withChildren([
                Permission::make('edit users')->withChildren([
                    Permission::make('create users'),
                    Permission::make('delete users'),
                    Permission::make('change passwords'),
                    Permission::make('edit roles'),
                ]),
            ]);
        });

        return $this;
    }

    protected function registerForms()
    {
        Permission::register('configure forms');

        Permission::register('view {form} form submissions', function ($permission) {
            $permission->withChildren([
                Permission::make('delete {form} form submissions')
            ])->withReplacements('form', function () {
                return Form::all()->map(function ($form) {
                    return ['value' => $form->name(), 'label' => $form->title()];
                });
            });
        });

        return $this;
    }
}
