<?php

namespace Statamic\Auth;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\Collection;
use Statamic\Facades\Form;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Nav;
use Statamic\Facades\Permission;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Utility;

class CorePermissions
{
    public function boot()
    {
        $this->group('cp', function () {
            $this->register('access cp');
            $this->register('configure fields');
            $this->register('configure addons');
            $this->register('manage preferences');
        });

        $this->group('collections', function () {
            $this->registerCollections();
        });

        $this->group('navigation', function () {
            $this->registerNavigation();
        });

        $this->group('globals', function () {
            $this->registerGlobals();
        });

        $this->group('taxonomies', function () {
            $this->registerTaxonomies();
        });

        $this->group('assets', function () {
            $this->registerAssets();
        });

        $this->group('users', function () {
            $this->registerUsers();
        });

        $this->group('updates', function () {
            $this->registerUpdates();
        });

        $this->group('forms', function () {
            $this->registerForms();
        });

        $this->group('utilities', function () {
            $this->registerUtilities();
        });

        $this->register('resolve duplicate ids');
        $this->register('view graphql');
    }

    protected function registerCollections()
    {
        $this->register('configure collections');

        $this->register('view {collection} entries', function ($permission) {
            $this->permission($permission)->children([
                $this->permission('edit {collection} entries')->children([
                    $this->permission('create {collection} entries'),
                    $this->permission('delete {collection} entries'),
                    $this->permission('publish {collection} entries'),
                    $this->permission('reorder {collection} entries'),
                    $this->permission('edit other authors {collection} entries')->children([
                        $this->permission('publish other authors {collection} entries'),
                        $this->permission('delete other authors {collection} entries'),
                    ]),
                ]),
            ])->replacements('collection', function () {
                return Collection::all()->map(function ($collection) {
                    return ['value' => $collection->handle(), 'label' => $collection->title()];
                });
            });
        });
    }

    protected function registerNavigation()
    {
        $this->register('configure navs');

        $this->register('view {nav} nav', function ($permission) {
            $this->permission($permission)->children([
                $this->permission('edit {nav} nav'),
            ])->replacements('nav', function () {
                return Nav::all()->map(function ($nav) {
                    return ['value' => $nav->handle(), 'label' => $nav->title()];
                });
            });
        });
    }

    protected function registerGlobals()
    {
        $this->register('configure globals');

        $this->register('edit {global} globals', function ($permission) {
            $permission->replacements('global', function () {
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
            $this->permission($permission)->children([
                $this->permission('edit {taxonomy} terms')->children([
                    $this->permission('create {taxonomy} terms'),
                    $this->permission('delete {taxonomy} terms'),
                ]),
            ])->replacements('taxonomy', function () {
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
            $this->permission($permission)->children([
                $this->permission('upload {container} assets'),
                $this->permission('edit {container} assets')->children([
                    $this->permission('move {container} assets'),
                    $this->permission('rename {container} assets'),
                    $this->permission('delete {container} assets'),
                ]),
            ])->replacements('container', function () {
                return AssetContainer::all()->map(function ($container) {
                    return ['value' => $container->handle(), 'label' => $container->title()];
                });
            });
        });
    }

    protected function registerUpdates()
    {
        $this->register('view updates', function ($permission) {
            $this->permission($permission)->children([
                $this->permission('perform updates'),
            ]);
        });
    }

    protected function registerUsers()
    {
        $this->register('view users', function ($permission) {
            $this->permission($permission)->children([
                $this->permission('edit users')->children([
                    $this->permission('create users'),
                    $this->permission('delete users'),
                    $this->permission('change passwords'),
                    $this->permission('assign user groups'),
                    $this->permission('assign roles'),
                ]),
            ]);
        });

        $this->register('edit user groups');
        $this->register('edit roles');
    }

    protected function registerForms()
    {
        $this->register('configure forms');

        $this->register('view {form} form submissions', function ($permission) {
            $this->permission($permission)->children([
                $this->permission('delete {form} form submissions'),
            ])->replacements('form', function () {
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
                    ->label(__('statamic::permissions.access_utility', ['title' => $utility->title()]))
                    ->description(__('statamic::permissions.access_utility_desc', ['title' => $utility->title()]));
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

        $label = __('statamic::permissions.'.str_replace(' ', '_', $permission->value()));
        $description = __($descKey = 'statamic::permissions.'.str_replace(' ', '_', $permission->value().'_desc'));
        $description = $description === $descKey ? null : $description;

        return $permission->label($label)->description($description);
    }

    protected function group($name, $callback)
    {
        return Permission::group($name, __('statamic::permissions.group_'.$name), $callback);
    }
}
