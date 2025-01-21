<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\AssetContainer;
use Statamic\Support\Str;

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
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'max_items' => [
                        'display' => __('Max Items'),
                        'instructions' => __('statamic::messages.max_items_instructions'),
                        'min' => 1,
                        'type' => 'integer',
                    ],
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
                    'container' => [
                        'display' => __('Container'),
                        'instructions' => __('statamic::fieldtypes.asset_folders.config.container'),
                        'type' => 'asset_container',
                        'max_items' => 1,
                    ],
                ],
            ],
        ];
    }

    protected function toItemArray($id, $site = null)
    {
        return ['title' => $id, 'id' => $id];
    }

    public function getIndexItems($request)
    {
        return AssetContainer::find($request->container)
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
