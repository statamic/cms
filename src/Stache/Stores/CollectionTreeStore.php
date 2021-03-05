<?php

namespace Statamic\Stache\Stores;

use Statamic\Structures\CollectionTree;

class CollectionTreeStore extends NavTreeStore
{
    public function key()
    {
        return 'collection-trees';
    }

    protected function newTreeClassByPath($path)
    {
        [$site, $handle] = $this->parseTreePath($path);

        return (new CollectionTree)
            ->initialPath($path)
            ->locale($site)
            ->handle($handle);
    }

    public function getItemKey($item)
    {
        return str_replace('collection::', '', parent::getItemKey($item));
    }
}
