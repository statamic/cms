<?php

namespace Statamic\Auth;

use Statamic\Facades\Form;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Structure;
use Statamic\Facades\Collection;
use Statamic\Facades\Permission;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Utility;

class CorePermissions
{
    public function boot()
    {
        Permission::group('cp', function () {
            Permission::register('access cp');
        });

        Permission::group('collections', function () {
            $this->registerCollections();
        });

        Permission::group('structures', function () {
            $this->registerStructures();
        });

        Permission::group('globals', function () {
            $this->registerGlobals();
        });

        Permission::group('taxonomies', function () {
            $this->registerTaxonomies();
        });

        Permission::group('assets', function () {
            $this->registerAssets();
        });

        Permission::group('users', function () {
            $this->registerUsers();
        });

        Permission::group('updates', function () {
            $this->registerUpdates();
        });

        Permission::group('forms', function () {
            $this->registerForms();
        });

        Permission::group('utilities', function () {
            $this->registerUtilities();
        });
    }

    protected function registerCollections()
    {
        Permission::register('configure collections');

        Permission::register('view {collection} entries', function ($permission) {
            $permission->withChildren([
                Permission::make('edit {collection} entries')->withChildren([
                    Permission::make('create {collection} entries'),
                    Permission::make('delete {collection} entries'),
                    Permission::make('publish {collection} entries'),
                    Permission::make('reorder {collection} entries')
                ])
            ])->withReplacements('collection', function () {
                return Collection::all()->map(function ($collection) {
                    return ['value' => $collection->handle(), 'label' => $collection->title()];
                });
            });
        });
    }

    protected function registerStructures()
    {
        Permission::register('configure structures');

        Permission::register('view {structure} structure', function ($permission) {
            $permission->withChildren([
                Permission::make('edit {structure} structure')
            ])->withReplacements('structure', function () {
                return Structure::all()->map(function ($structure) {
                    return ['value' => $structure->handle(), 'label' => $structure->title()];
                });
            });
        });
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
    }

    protected function registerTaxonomies()
    {
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
    }

    protected function registerAssets()
    {
        Permission::register('configure asset containers');

        Permission::register('view {container} assets', function ($permission) {
            $permission->withChildren([
                Permission::make('upload {container} assets'),
                Permission::make('edit {container} assets')->withChildren([
                    Permission::make('move {container} assets'),
                    Permission::make('rename {container} assets'),
                    Permission::make('delete {container} assets')
                ])
            ])->withReplacements('container', function () {
                return AssetContainer::all()->map(function ($container) {
                    return ['value' => $container->handle(), 'label' => $container->title()];
                });
            });
        });
    }

    protected function registerUpdates()
    {
        Permission::register('view updates', function ($permission) {
            $permission->withChildren([
                Permission::make('perform updates'),
            ]);
        });
    }

    protected function registerUsers()
    {
        Permission::register('view users', function ($permission) {
            $permission->withChildren([
                Permission::make('edit users')->withChildren([
                    Permission::make('create users'),
                    Permission::make('delete users'),
                    Permission::make('change passwords'),
                    Permission::make('edit user groups'),
                    Permission::make('edit roles'),
                ]),
            ]);
        });
    }

    protected function registerForms()
    {
        Permission::register('configure forms');

        Permission::register('view {form} form submissions', function ($permission) {
            $permission->withChildren([
                Permission::make('delete {form} form submissions')
            ])->withReplacements('form', function () {
                return Form::all()->map(function ($form) {
                    return ['value' => $form->handle(), 'label' => $form->title()];
                });
            });
        });
    }

    protected function registerUtilities()
    {
        Utility::all()->each(function ($utility) {
            Permission::register("access {$utility->handle()} utility", function ($perm) use ($utility) {
                return $perm->withLabel(__('statamic::permissions.access_utility', ['title' => $utility->title()]));
            });
        });
    }
}
