<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Arr;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Site;
use Statamic\API\Term;
use Statamic\API\YAML;
use Statamic\API\Stache;
use Statamic\API\Taxonomy;
use Statamic\Stache\Indexes\Terms\Titles;
use Symfony\Component\Finder\SplFileInfo;
use Statamic\Stache\Indexes\Terms\Associations;

class TaxonomyTermsStore extends ChildStore
{
    protected $storeIndexes = [
        'slug',
        'taxonomy',
        'title' => Titles::class,
        'associations' => Associations::class,
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

    public function getItem($key)
    {
        $this->handleFileChanges();

        if ($item = $this->getCachedItem($key)) {
            return $item;
        }

        if ($path = $this->getPath($key)) {
            $item = $this->makeItemFromFile($path, File::get($path));
        } else {
            $item = Term::make($key)
                ->taxonomy($this->childKey())
                ->set('title', $this->index('title')->get($key));
        }

        $this->cacheItem($item);

        return $item;
    }

    public function sync($entry, $terms)
    {
        $taxonomy = $this->childKey();

        $terms = collect(Arr::wrap($terms))->mapWithKeys(function ($value) {
            return [Str::slug($value) => $value];
        });

        $associations = $this->index('associations');
        $titles = $this->index('title');
        $uris = $this->index('uri');

        foreach ($terms as $slug => $value) {
            $associations->push(compact('slug', 'entry'));

            $titles->put($slug, $value);

            $uris->put($slug, $this->makeTerm($taxonomy, $slug)->uri());
        }

        $associations->cache();
        $titles->cache();
        $uris->cache();
    }

    protected function makeTerm($taxonomy, $slug)
    {
        return Term::make($slug)
            ->taxonomy($taxonomy)
            ->set('title', $this->index('title')->get($slug));
    }

    public function handleFileChanges()
    {
        if ($this->fileChangesHandled) {
            return;
        }

        Taxonomy::findByHandle($this->childKey())
            ->collections()
            ->each(function ($collection) {
                Stache::store('entries')->store($collection->handle())->handleFileChanges();
            });

        parent::handleFileChanges();
    }
}
