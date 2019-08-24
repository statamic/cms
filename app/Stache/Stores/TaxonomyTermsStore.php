<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\Term;
use Statamic\API\YAML;
use Statamic\API\Collection;
use Symfony\Component\Finder\SplFileInfo;

class TaxonomyTermsStore extends ChildStore
{
    protected $storeIndexes = [
        'slug',
        'taxonomy',
    ];

    public function getFileFilter(SplFileInfo $file) {
        $dir = str_finish($this->directory, '/');
        $relative = $file->getPathname();

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        // if (! Taxonomy::findByHandle(explode('/', $relative)[0])) {
        //     return false;
        // }

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') > 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $site = Site::default()->handle();
        $taxonomy = pathinfo($path, PATHINFO_DIRNAME);
        $taxonomy = str_after($taxonomy, $this->parent->directory());

        return Term::make()
            ->taxonomy($taxonomy)
            ->slug(pathinfo(Path::clean($path), PATHINFO_FILENAME))
            ->initialPath($path)
            ->locale($site)
            ->data(YAML::parse($contents));
    }

    public function getItemKey($item)
    {
        return $item->slug();
    }
}
