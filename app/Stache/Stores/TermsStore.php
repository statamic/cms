<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Arr;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Term;
use Statamic\API\YAML;
use Statamic\API\Taxonomy;
use Illuminate\Support\Facades\Cache;
use Statamic\Stache\Exceptions\StoreExpiredException;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as TaxonomyContract;

class TermsStore extends AggregateStore
{
    protected $associations = [];
    protected $uris = [];
    protected $titles = [];

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

    public function getStoreById($id)
    {
        return $this->store(explode('::', $id, 2)[0]);
    }

    public function getIdFromUri($uri, $site = null)
    {
        if ($id = parent::getIdFromUri($uri, $site)) {
            return $id;
        }

        $site = $site ?? $this->stache->sites()->first();

        $uris = array_flip($this->uris[$site]);

        return $uris[$uri] ?? null;
    }


    public function loadMeta($data)
    {
        $this->associations = $data['associations'];
        $this->uris = $data['uris'];
        $this->titles = $data['titles'];
    }

    public function cache()
    {
        parent::cache();

        Cache::forever($this->getMetaCacheKey(), $this->getCacheableMeta());
    }

    public function getCacheableMeta()
    {
        return [
            'associations' => $this->associations,
            'uris' => $this->uris,
            'titles' => $this->titles,
        ];
    }

    public function getMetaFromCache()
    {
        return array_merge(parent::getMetaFromCache(), [
            $this->key() => Cache::get($this->getMetaCacheKey(), $this->getCacheableMeta())
        ]);
    }

    public function getAssociations()
    {
        return $this->associations;
    }

    public function sync($entry, $taxonomy, $terms)
    {
        $terms = collect(Arr::wrap($terms))->mapWithKeys(function ($value) {
            return [Str::slug($value) => $value];
        });

        foreach ($terms as $slug => $value) {
            $key = "{$taxonomy}::{$slug}";

            $this->associations[$taxonomy][$slug][] = $entry;
            $this->titles[$key] = $value;

            $term = $this->makeTerm($taxonomy, $slug);
            foreach ($this->stache->sites() as $site) {
                $this->uris[$site][$key] = $term->in($site)->uri();
            }
        }

        // Remove any unused terms
        foreach ($this->associations[$taxonomy] ?? [] as $term => $associations) {
            if ($terms->has($term)) {
                continue;
            }

            $associations = array_values(array_diff($associations, [$entry]));

            if (empty($associations)) {
                unset($this->associations[$taxonomy][$term]);
            } else {
                $this->associations[$taxonomy][$term] = $associations;
            }
        }
    }

    public function getTerm($id)
    {
        if ($term = $this->getStoreById($id)->getItem($id)) {
            return $term;
        }

        [$taxonomy, $slug] = explode('::', $id);

        if (Arr::has($this->associations[$taxonomy] ?? [], $slug)) {
            return $this->makeTerm($taxonomy, $slug);
        }

        return null;
    }

    public function getAllTerms()
    {
        return $this->stores()->keys()->flatMap(function ($taxonomy) {
            return $this->getTaxonomyTerms($taxonomy);
        });
    }

    public function getTaxonomyTerms($handle)
    {
        $taxonomy = Taxonomy::findByHandle($handle);

        // First get all the terms that exist on disk.
        $terms = $this->store($handle)->getItems();

        // Then add any on-the-fly terms that don't exist on disk.
        foreach ($this->associations[$handle] ?? [] as $slug => $associations) {
            if ($terms->has("{$handle}::{$slug}")) {
                continue;
            }

            $term = $this->makeTerm($taxonomy, $slug);

            $terms->put("{$handle}::{$slug}", $term);
        }

        return $terms;
    }

    protected function makeTerm($taxonomy, $slug)
    {
        $taxonomy = $taxonomy instanceof TaxonomyContract
            ? $taxonomy
            : Taxonomy::findByHandle($taxonomy);

        return Term::make($slug)
            ->taxonomy($taxonomy)
            ->set('title', $this->titles["{$taxonomy->handle()}::{$slug}"]);
    }
}
