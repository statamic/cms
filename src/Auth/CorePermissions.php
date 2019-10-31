<?php

namespace Statamic\Auth;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Permission;
use Statamic\Facades\Structure;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Utility;

class CorePermissions
{
    public function boot()
    {
        Permission::group('cp', function () {
            $this->register('access cp');
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
        $this->register('configure collections');

        $this->register('view {collection} entries', function ($permission) {
            $this->permission($permission)->withChildren([
                $this->permission('edit {collection} entries')->withChildren([
                    $this->permission('create {collection} entries'),
                    $this->permission('delete {collection} entries'),
                    $this->permission('publish {collection} entries'),
                    $this->permission('reorder {collection} entries')
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
        $this->register('configure structures');

        $this->register('view {structure} structure', function ($permission) {
            $this->permission($permission)->withChildren([
                $this->permission('edit {structure} structure')
            ])->withReplacements('structure', function () {
                return Structure::all()->map(function ($structure) {
                    return ['value' => $structure->handle(), 'label' => $structure->title()];
                });
            });
        });
    }

    protected function registerGlobals()
    {
        $this->register('configure globals');

        $this->register('edit {global} globals', function ($permission) {
            $permission->withReplacements('global', function () {
                return GlobalSet::all()->map(function ($global) {
                    return ['value' => $global->handle(), 'label' => $global->title()];
                });
            });
        });
    }

    protected function registerTaxonomies()
    {
        $this->register('configure taxonomies');

        $this->register('view {taxonomy} terms', function ($permission) {
            $this->permission($permission)->withChildren([
                $this->permission('edit {taxonomy} terms')->withChildren([
                    $this->permission('create {taxonomy} terms'),
                    $this->permission('delete {taxonomy} terms')
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
        $this->register('configure asset containers');

        $this->register('view {container} assets', function ($permission) {
            $this->permission($permission)->withChildren([
                $this->permission('upload {container} assets'),
                $this->permission('edit {container} assets')->withChildren([
                    $this->permission('move {container} assets'),
                    $this->permission('rename {container} assets'),
                    $this->permission('delete {container} assets')
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
        $this->register('view updates', function ($permission) {
            $this->permission($permission)->withChildren([
                $this->permission('perform updates'),
            ]);
        });
    }

    protected function registerUsers()
    {
        $this->register('view users', function ($permission) {
            $this->permission($permission)->withChildren([
                $this->permission('edit users')->withChildren([
                    $this->permission('create users'),
                    $this->permission('delete users'),
                    $this->permission('change passwords'),
                    $this->permission('edit user groups'),
                    $this->permission('edit roles'),
                ]),
            ]);
        });
    }

    protected function registerForms()
    {
        $this->register('configure forms');

        $this->register('view {form} form submissions', function ($permission) {
            $this->permission($permission)->withChildren([
                $this->permission('delete {form} form submissions')
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
                return $perm
                    ->withLabel(__('statamic::permissions.access_utility', ['title' => $utility->title()]))
                    ->withDescription(__('statamic::permissions.access_utility_desc', ['title' => $utility->title()]));
            });
        });
    }

    protected function register($permission, $callback = null)
    {
        $permission = $this->permission($permission);

        return Permission::register($permission, $callback);
    }

    protected function permission($permission)
    {
        if (is_string($permission)) {
            $permission = Permission::make($permission);
        }

        return $permission->withTranslation(
            'statamic::permissions.'.str_replace(' ', '_', $permission->value())
        );
    }
}
