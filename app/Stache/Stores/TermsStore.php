<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Arr;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Term;
use Statamic\API\YAML;
use Statamic\API\Taxonomy;
use Statamic\Stache\Exceptions\StoreExpiredException;

class TermsStore extends AggregateStore
{
    protected $associations = [
        'tags::weird' => [
            'd962e620-ebc8-45d3-99e9-2efe4a3f4576', // weird al
        ],
        'tags::accordion' => [
            'd962e620-ebc8-45d3-99e9-2efe4a3f4576', // weird al
        ],
        'tags::eighties' => [
            'd962e620-ebc8-45d3-99e9-2efe4a3f4576', // weird al
            'ae10674b-d38c-49a9-b97a-a5d0d947c845', // basic
            'c2b3d164-c953-4df2-9efc-f7f72673d0a2', // commodore 64
        ],
    ];

    public function key()
    {
        return 'terms';
    }

    public function getItemsFromCache($cache)
    {
        $terms = collect();

        if ($cache->isEmpty()) {
            return $terms;
        }

        $taxonomy = Taxonomy::findByHandle(Arr::first($cache)['taxonomy']);

        // The taxonomy has been deleted.
        throw_unless($taxonomy, new StoreExpiredException);

        return $cache->map(function ($item, $id) use ($taxonomy) {
            return Term::make()
                ->taxonomy($taxonomy)
                ->slug($item['slug'])
                ->initialPath($item['path'])
                ->data($item['data']);
        });
    }

    public function getCacheableMeta()
    {

    }

    public function getCacheableItems()
    {

    }


    public function createItemFromFile($path, $contents)
    {
        $taxonomy = pathinfo($path, PATHINFO_DIRNAME);
        $taxonomy = str_after($taxonomy, $this->directory);

        // Support terms within subdirectories at any level.
        if (str_contains($taxonomy, '/')) {
            $taxonomy = str_before($taxonomy, '/');
        }

        return Term::make()
            ->taxonomy(Taxonomy::findByHandle($taxonomy))
            ->slug(pathinfo(Path::clean($path), PATHINFO_FILENAME))
            ->initialPath($path)
            ->data(YAML::parse($contents));
    }

    public function getItemKey($item, $path)
    {
        return $item->taxonomyHandle() . '::' . $item->id();
    }

    public function filter($file)
    {
        $dir = str_finish($this->directory, '/');
        $relative = $file->getPathname();

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        if (! Taxonomy::findByHandle(explode('/', $relative)[0])) {
            return false;
        }

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') > 0;
    }

    public function save($term)
    {
        File::put($term->path(), $term->fileContents());
    }

    public function delete($term)
    {
        File::delete($term->path());
    }

    public function getAssociations()
    {
        return $this->associations;
    }
}
