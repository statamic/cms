<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Collection;
use Statamic\Facades\Path;
use Statamic\Structures\CollectionTree;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class CollectionTreeStore extends NavTreeStore
{
    public function key()
    {
        return 'collection-trees';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        if (! parent::getItemFilter($file)) {
            return false;
        }

        [, $handle] = $this->parseTreePath(Path::tidy($file->getPathname()));

        if (! ($collection = Collection::findByHandle($handle))) {
            return false;
        }

        return $collection->hasStructure();
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
        $key = parent::getItemKey($item);

        if (Str::startsWith($key, 'collection::')) {
            $key = Str::after($key, 'collection::');
        }

        return $key;
    }
}
