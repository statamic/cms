<?php

namespace Statamic\Actions;

use Closure;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class ReplaceAsset extends Action
{
    protected $icon = 'replace';

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
        return 'statamic::messages.asset_replace_confirmation';
    }

    public function run($assets, $values)
    {
        $originalAsset = $assets->first();

        $newAsset = Facades\Asset::find($this->context['container'].'::'.$values['new_asset']);

        $newAsset->replace($originalAsset, $values['delete_original']);

        return [
            'ids' => [$newAsset->id()],
        ];
    }

    protected function fieldItems()
    {
        return [
            'new_asset' => [
                'display' => __('New Asset'),
                'type' => 'assets',
                'container' => $this->context['container'],
                'folder' => $this->context['folder'] ?? null,
                'max_files' => 1,
                'validate' => ['required', $this->differentAssetRule()],
                'mode' => 'list',
                'restrict' => true,
                'allow_uploads' => true,
                'show_filename' => true,
            ],
            'delete_original' => [
                'display' => __('Delete Original Asset'),
                'type' => 'toggle',
                'default' => true,
            ],
        ];
    }

    private function differentAssetRule(): Closure
    {
        $asset = $this->items->first();

        return function (string $attribute, mixed $value, Closure $fail) use ($asset) {
            if ($asset && in_array($asset->id(), Arr::wrap($value))) {
                $fail('statamic::validation.asset_replace_same')->translate();
            }
        };
    }
}
