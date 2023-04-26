<?php

namespace Statamic\Actions;

use Statamic\Assets\ReplacementFile;
use Statamic\Contracts\Assets\Asset;
use Statamic\Exceptions\FileExtensionMismatch;
use Statamic\Exceptions\ValidationException;

class ReuploadAsset extends Action
{
    public static function title()
    {
        return __('Reupload');
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
        return $user->can('store', $asset->container());
    }

    public function buttonText()
    {
        /** @translation */
        return 'Reupload';
    }

    public function confirmationText()
    {
        /** @translation */
        return 'statamic::messages.asset_reupload_confirmation';
    }

    public function warningText()
    {
        /** @translation */
        return 'statamic::messages.asset_reupload_warning';
    }

    public function run($assets, $values)
    {
        $asset = $assets->first();

        $file = new ReplacementFile('statamic/file-uploads/'.$values['file']);

        try {
            $asset->reupload($file);
        } catch (FileExtensionMismatch $e) {
            throw ValidationException::withMessages(['file' => trans('statamic::validation.mimes', ['values' => $asset->extension()])]);
        }

        $urls = [
            $asset->thumbnailUrl('small'),
            $asset->absoluteUrl(),
        ];

        return [
            'callback' => ['bustAndReloadImageCaches', $urls],
            'ids' => [$asset->id()],
        ];
    }

    protected function fieldItems()
    {
        return [
            'file' => [
                'type' => 'files',
                'max_files' => 1,
                'validate' => ['required'],
                'container' => $this->items->first()->container()->handle(),
            ],
        ];
    }
}
