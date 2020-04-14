<?php

namespace Statamic\Assets;

use Statamic\Data\AbstractAugmented;

class AugmentedAssetContainer extends AbstractAugmented
{
    public function keys()
    {
        return [
            'title',
            'handle',
            'disk',
            'blueprint',
            'search_index',
            'api_url',
            'assets',
        ];
    }

    public function augmentableKeys()
    {
        // Override this to prevent the default behavior of looking at the
        // blueprint for the keys. Those are for the assets, not the container.
        return $this->keys();
    }

    protected function disk()
    {
        return $this->data->diskHandle();
    }

    protected function searchIndex()
    {
        return optional($this->data->searchIndex())->name();
    }

    protected function assets()
    {
        return $this->data->queryAssets();
    }
}
