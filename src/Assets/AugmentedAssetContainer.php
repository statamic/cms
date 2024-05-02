<?php

namespace Statamic\Assets;

use Statamic\Data\AbstractAugmented;

class AugmentedAssetContainer extends AbstractAugmented
{
    public function keys()
    {
        return [
            'id',
            'title',
            'handle',
            'disk',
            'blueprint',
            'search_index',
            'api_url',
            'assets',
        ];
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
