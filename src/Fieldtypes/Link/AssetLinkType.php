<?php

namespace Statamic\Fieldtypes\Link;

use Statamic\Facades;
use Statamic\Fields\Field;

use function Statamic\trans as __;

class AssetLinkType extends LinkType
{
    protected ?string $icon = 'assets';

    public function title(): string
    {
        return __('Asset');
    }

    public function configFieldItems(): array
    {
        return [
            'container' => [
                'display' => __('Container'),
                'instructions' => __('statamic::fieldtypes.link.config.container'),
                'type' => 'asset_container',
                'mode' => 'select',
                'max_items' => 1,
                'width' => '50',
            ],
        ];
    }

    public function resolve(string $id, $parent = null, bool $localize = false): mixed
    {
        return Facades\Asset::find($id);
    }

    public function fieldtype(Field $field): array
    {
        return [
            'type' => 'assets',
            'max_files' => 1,
            'mode' => 'list',
            'container' => $field->get('container'),
        ];
    }

    public function visible(Field $field): bool
    {
        return $field->get('container') !== null;
    }
}
