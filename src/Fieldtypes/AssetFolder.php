<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;
use Statamic\Support\Str;

use function Statamic\trans as __;

class AssetFolder extends Relationship
{
    protected $component = 'asset_folder';
    protected $statusIcons = false;
    protected $canEdit = false;
    protected $canCreate = false;
    protected $selectable = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'container' => [
                        'display' => __('Container'),
                        'instructions' => __('statamic::fieldtypes.asset_folders.config.container'),
                        'type' => 'asset_container',
                        'max_items' => 1,
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'mode' => [
                        'display' => __('UI Mode'),
                        'instructions' => __('statamic::fieldtypes.relationship.config.mode'),
                        'type' => 'radio',
                        'default' => 'default',
                        'options' => [
                            'default' => __('Stack Selector'),
                            'select' => __('Select Dropdown'),
                            'typeahead' => __('Typeahead Field'),
                        ],
                    ],
                ],
            ],
            [
                'display' => __('Boundaries & Limits'),
                'fields' => [
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                    ],
                ],
            ],
        ];
    }

    protected function authorizeItemData($id): bool
    {
        // No static container configured (dynamic/sibling-container mode); the by-id value
        // only echoes the submitted folder path back, so there is nothing to authorize.
        // Folder enumeration is gated separately, on the runtime container, in getIndexItems().
        if (! $container = $this->config('container')) {
            return true;
        }

        return $this->authorizeViewable(AssetContainer::find($container));
    }

    protected function toItemArray($id, $site = null)
    {
        return ['title' => $id, 'id' => $id];
    }

    public function getIndexItems($request)
    {
        $container = AssetContainer::find($request->container);

        // No/unviewable container: return an empty folder list rather than throwing.
        if (! $container || ! User::current()->can('view', $container)) {
            return collect();
        }

        return $container
            ->folders()
            ->map(function ($folder) {
                return ['id' => $folder, 'title' => $folder];
            })
            ->prepend(['id' => '/', 'title' => '/'])
            ->when($request->search, function ($folders, $search) {
                return $folders->filter(fn ($folder) => Str::contains($folder['title'], $search));
            })
            ->values();
    }
}
