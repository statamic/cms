<?php

namespace Statamic\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
use Illuminate\Support\Facades\Cache;
use Statamic\Entries\GetSlugFromPath;
use Statamic\Facades\File;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;
use Statamic\Facades\YAML;
use Statamic\Stache\Indexes;
use Statamic\Stache\Indexes\Terms\Value;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class TaxonomyTermsStore extends ChildStore
{
    protected $valueIndex = Value::class;
    protected $storeIndexes = [
        'slug',
        'taxonomy',
        'associations' => Indexes\Terms\Associations::class,
        'site' => Indexes\Terms\Site::class,
    ];

    public function getItemFilter(SplFileInfo $file)
    {
        $dir = str_finish($this->directory(), '/');
        $relative = $file->getPathname();

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        // if (! Taxonomy::findByHandle(explode('/', $relative)[0])) {
        //     return false;
        // }

        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $taxonomy = pathinfo($path, PATHINFO_DIRNAME);
        $taxonomy = str_after($taxonomy, $this->parent->directory());

        $data = YAML::file($path)->parse($contents);

        $term = Term::make()
            ->taxonomy($taxonomy)
            ->slug((new GetSlugFromPath)($path))
            ->initialPath($path)
            ->blueprint($data['blueprint'] ?? null);

        foreach (Arr::pull($data, 'localizations', []) as $locale => $localeData) {
            $term->dataForLocale($locale, $localeData);
        }

        $term->dataForLocale($term->defaultLocale(), $data);
        $term->syncOriginal();

        return $term;
    }

    public function getItemKey($item)
    {
        return $item->locale().'::'.$item->inDefaultLocale()->slug();
    }

    public function getItem($key)
    {
        $this->handleFileChanges();

        if ($item = $this->getCachedItem($key)) {
            return $item;
        }

        [$site, $slug] = explode('::', $key);

        if ($path = $this->getPath($key)) {
            $item = $this->makeItemFromFile($path, File::get($path))->in($site);
        } else {
            $item = Term::make($slug)
                ->taxonomy($this->childKey())
                ->set('title', $this->index('title')->get($key))
                ->in($site);
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

        $indexes = $this->resolveIndexes()->except('associations');
        $associations = $this->index('associations');

        $associations->forgetEntry($entry->id());

        foreach ($terms as $slug => $value) {
            $associations->push([
                'value' => $value,
                'slug' => $slug,
                'entry' => $entry->id(),
                'collection' => $entry->collectionHandle(),
                'site' => $entry->locale(),
            ]);
        }
        $associations->cache();

        foreach ($terms as $slug => $value) {
            $term = Term::find("$taxonomy::$slug") ?? $this->makeTerm($taxonomy, $slug);
            $indexes->each->updateItem($term);
        }
    }

    protected function makeTerm($taxonomy, $slug)
    {
        return Term::make($slug)
            ->taxonomy($taxonomy)
            ->set('title', $this->index('title')->get($slug));
    }

    public function handleFileChanges()
    {
        if ($this->fileChangesHandled || ! config('statamic.stache.watcher')) {
            return;
        }

        Taxonomy::findByHandle($this->childKey())
            ->collections()
            ->each(function ($collection) {
                Stache::store('entries')->store($collection->handle())->handleFileChanges();
            });

        parent::handleFileChanges();
    }

    public function paths()
    {
        if ($this->paths) {
            return $this->paths;
        }

        if ($paths = Cache::get($this->pathsCacheKey())) {
            return $this->paths = collect($paths);
        }

        $files = Traverser::filter([$this, 'getItemFilter'])->traverse($this);

        $paths = $files->mapWithKeys(function ($timestamp, $path) {
            $term = $this->makeItemFromFile($path, File::get($path));

            return $term->localizations()->flatMap(function ($localization, $locale) use ($path) {
                $this->cacheItem($localization);

                return [$this->getItemKey($localization) => $path];
            })->all();
        });

        $this->cachePaths($paths);

        return $paths;
    }

    protected function getKeyFromPath($path)
    {
        return $this->paths()->filter(function ($p) use ($path) {
            return \Statamic\Support\Str::endsWith($p, $path);
        })->keys();
    }

    public function save($term)
    {
        $this->writeItemToDisk($term);

        foreach ($term->localizations() as $item) {
            $key = $this->getItemKey($item);

            $this->forgetItem($key);

            $this->setPath($key, $item->path());

            $this->resolveIndexes()->each->updateItem($item);

            $this->cacheItem($item);
        }
    }

    protected function getItemFromModifiedPath($path)
    {
        return parent::getItemFromModifiedPath($path)->localizations()->all();
    }
}
