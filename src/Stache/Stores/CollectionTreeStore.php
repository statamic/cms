<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Collection;
use Statamic\Facades\Path;
use Statamic\Structures\CollectionTree;
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

    /**
     * A tree file changed on disk (a git pull, a deploy, a hand edit) and the watcher picked it up.
     * Saving a tree through the API updates the entries it affects in the CollectionTreeSaved
     * listeners, but there's no saved event here, so update the collection's entries directly.
     * The previous tree is already gone from the cache, so there's no diff to narrow this down.
     */
    protected function handleModifiedItem($tree)
    {
        if (! $collection = $tree->collection()) {
            return;
        }

        $collection->updateEntryParent();
        $collection->updateEntryOrder();

        // Same conditions as UpdateStructuredEntryUris: a single-depth (orderable) structure
        // never affects uris, and without a route there are none.
        if (! $collection->orderable() && $collection->route($tree->locale())) {
            $collection->updateEntryUris();
        }
    }

    protected function newTreeClassByPath($path)
    {
        [$site, $handle] = $this->parseTreePath($path);

        return (new CollectionTree)
            ->initialPath($path)
            ->locale($site)
            ->handle($handle);
    }
}
