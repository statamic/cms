<?php

namespace Statamic\Permissions;

use Statamic\API\AssetContainer;
use Statamic\API\Collection;
use Statamic\API\GlobalSet;
use Statamic\API\Str;
use Statamic\API\Taxonomy;

class Permissions
{
    private $permissions = [];

    public function all($wildcards = false)
    {
        if ($wildcards) {
            return $this->permissions;
        }

        return collect($this->permissions)->reject(function ($permission) {
            return Str::contains($permission, '*');
        })->all();
    }

    public function build()
    {
        $permissions = [
            'super',                           // can do everything
            'cp:access',                       // access the cp
            'content:view_drafts_on_frontend', // can view drafts
            'pages:edit',                      // can edit existing pages
            'pages:create',                    // can create new pages
            'pages:delete',                    // can delete pages
            'pages:reorder',                   // can reorder pages
            'forms',                           // can access forms
            'updater',                         // can access the updater to see updates
            'updater:update',                  // can perform updates
            'importer',                        // can import data
            'users:edit',                      // can edit users
            'users:create',                    // can create users
            'users:delete',                    // can delete users
            'resolve_duplicates',              // can resolve duplicate ids
        ];

        $permissions = array_merge($permissions, self::buildCollectionsPermissions());
        $permissions = array_merge($permissions, self::buildTaxonomiesPermissions());
        $permissions = array_merge($permissions, self::buildGlobalsPermissions());
        $permissions = array_merge($permissions, self::buildAssetsPermissions());

        $this->permissions = $permissions;
    }

    private function buildCollectionsPermissions()
    {
        $permissions = ['collections:*:edit'];

        foreach (Collection::handles() as $collection) {
            $permissions[] = "collections:{$collection}:edit";
            $permissions[] = "collections:{$collection}:create";
            $permissions[] = "collections:{$collection}:delete";
        }

        return $permissions;
    }

    private function buildTaxonomiesPermissions()
    {
        $permissions = ['taxonomies:*:edit'];

        foreach (Taxonomy::handles() as $taxonomy) {
            $permissions[] = "taxonomies:{$taxonomy}:edit";
            $permissions[] = "taxonomies:{$taxonomy}:create";
            $permissions[] = "taxonomies:{$taxonomy}:delete";
        }

        return $permissions;
    }

    private function buildGlobalsPermissions()
    {
        $permissions = ['globals:*:edit'];

        foreach (GlobalSet::all() as $global) {
            $permissions[] = "globals:{$global->slug()}:edit";
        }

        return $permissions;
    }

    private function buildAssetsPermissions()
    {
        $permissions = ['assets:*:edit'];

        foreach (AssetContainer::all() as $container) {
            $permissions[] = "assets:{$container->id()}:edit";
            $permissions[] = "assets:{$container->id()}:create";
            $permissions[] = "assets:{$container->id()}:delete";
        }

        return $permissions;
    }

    public function structured()
    {
        $structure = [
            'general' => ['cp:access', 'content:view_drafts_on_frontend', 'resolve_duplicates'],
            'pages' => [
                'pages:edit' => ['pages:create', 'pages:delete', 'pages:reorder']
            ]
        ];

        foreach (Collection::handles() as $collection) {
            $structure['collections:'.$collection] = [
                "collections:{$collection}:edit" => [
                    "collections:{$collection}:create",
                    "collections:{$collection}:delete"
                ]
            ];
        }

        foreach (Taxonomy::handles() as $taxonomy) {
            $structure['taxonomies:'.$taxonomy] = [
                "taxonomies:{$taxonomy}:edit" => [
                    "taxonomies:{$taxonomy}:create",
                    "taxonomies:{$taxonomy}:delete"
                ]
            ];
        }

        foreach (GlobalSet::all() as $set) {
            $structure['globals:'.$set->slug()] = ["globals:{$set->slug()}:edit"];
        }

        foreach (AssetContainer::all() as $container) {
            $structure['assets:'.$container->id()] = [
                "assets:{$container->id()}:edit" => [
                    "assets:{$container->id()}:create",
                    "assets:{$container->id()}:delete"
                ]
            ];
        }

        $structure = array_merge($structure, [
            'forms' => ['forms'],
            'updater' => [
                'updater' => ['updater:update']
            ],
            'importer' => ['importer'],
            'users' => [
                'users:edit' => ['users:create', 'users:delete']
            ]
        ]);

        return $structure;
    }
}
