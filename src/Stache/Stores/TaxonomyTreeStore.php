<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Path;
use Statamic\Facades\Taxonomy;
use Statamic\Structures\TaxonomyTree;
use Symfony\Component\Finder\SplFileInfo;

class TaxonomyTreeStore extends NavTreeStore
{
    public function key()
    {
        return 'taxonomy-trees';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        if (! parent::getItemFilter($file)) {
            return false;
        }

        [, $handle] = $this->parseTreePath(Path::tidy($file->getPathname()));

        if (! ($taxonomy = Taxonomy::findByHandle($handle))) {
            return false;
        }

        return $taxonomy->hasStructure();
    }

    protected function newTreeClassByPath($path)
    {
        [$site, $handle] = $this->parseTreePath($path);

        return (new TaxonomyTree)
            ->initialPath($path)
            ->locale($site)
            ->handle($handle);
    }
}
