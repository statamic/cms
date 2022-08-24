<?php

namespace Statamic\Actions;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades;

class ReplaceAsset extends Action
{
    public static function title()
    {
        return __('Replace');
    }

    public function visibleTo($item)
    {
        return $item instanceof Asset;
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function authorize($user, $asset)
    {
        return $user->can('replace', $asset);
    }

    public function buttonText()
    {
        /** @translation */
        return 'Replace Asset';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'Are you sure you want to replace this asset and all of its references?';
    }

    public function run($assets, $values)
    {
        $originalAsset = $assets->first();
        $newAsset = Facades\Asset::find($values['new_asset'][0]);

        $newAsset->replace($originalAsset, $values['delete_original'], $values['preserve_filename']);

        $urls = [
            $newAsset->thumbnailUrl('small'),
            $newAsset->absoluteUrl(),
        ];

        return [
            'message' => false,
            'callback' => ['bustAndReloadImageCaches', $urls],
        ];
    }

    protected function fieldItems()
    {
        return [
            'new_asset' => [
                'display' => __('New Asset'),
                'type' => 'assets',
                'container' => $this->context['container'],
                'folder' => $this->context['folder'],
                'max_files' => 1,
                'validate' => 'required',
                'mode' => 'list',
                'restrict' => false,
                'allow_uploads' => true,
                'show_filename' => true,
            ],
            'delete_original' => [
                'display' => __('Delete Original Asset'),
                'type' => 'toggle',
                'default' => true,
            ],
            'preserve_filename' => [
                'display' => __('Preserve Original Filename'),
                'type' => 'toggle',
                'default' => false,
                'instructions' => __('statamic::messages.replace_asset_preserve_filename_instructions'),
                'if' => [
                    'delete_original' => true,
                ],
            ],
        ];
    }
}
