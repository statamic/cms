<?php

namespace Statamic\Stache\Stores;

use Facades\Statamic\Stache\Traverser;
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
use Statamic\Taxonomies\EnsuresTermPaths;
use Symfony\Component\Finder\SplFileInfo;

class TaxonomyTermsStore extends ChildStore
{
    protected $valueIndex = Value::class;
    protected $storeIndexes = [
        'slug',
        'taxonomy',
        'order',
        'associations' => Indexes\Terms\Associations::class,
        'site' => Indexes\Terms\Site::class,
    ];

    public function getItemFilter(SplFileInfo $file)
    {
        $dir = Str::finish($this->directory(), '/');
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
        $taxonomy = Str::after($taxonomy, $this->parent->directory());

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

        // Association indexes create extra `{site}::{slug}` keys for every site an
        // entry uses the term. If the taxonomy isn't enabled in that site, there's
        // no path for the key — fall back to the term's file so we don't return a
        // title-from-slug stub that shadows the real term on save/reload.
        if ($path = $this->getPath($key) ?? $this->pathForSlug($slug)) {
            $term = $this->makeItemFromFile($path, File::get($path));
        } else {
            $term = Term::make($slug)
                ->taxonomy($this->childKey())
                ->set('title', $this->index('title')->get($key));
        }

        $term->syncOriginal();

        $item = $term->in($site);

        $this->cacheItem($item);

        return $item;
    }

    private function pathForSlug(string $slug): ?string
    {
        return $this->paths()->first(
            fn ($path, $key) => Str::after((string) $key, '::') === $slug
        );
    }

    public function sync($entry, $terms)
    {
        $taxonomy = $this->childKey();
        $paths = new EnsuresTermPaths;
        $lang = $entry->site()->lang();

        $terms = collect(Arr::wrap($terms))->mapWithKeys(function ($value) use ($paths, $lang) {
            if ($value === null || $value === '') {
                return [];
            }

            $slug = $paths->slugFromValue($value, $lang);

            return $slug ? [$slug => $value] : [];
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
        if ($this->fileChangesHandled || ! Stache::isWatcherEnabled()) {
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

        if ($paths = Stache::cacheStore()->get($this->pathsCacheKey())) {
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
        // Since we store terms by slug, if the slug changes it's technically
        // a completely new term, and we'll need to delete the existing one.
        if (($originalSlug = $term->getOriginal('slug')) && $originalSlug != $term->slug()) {
            $existing = Term::find($term->taxonomyHandle().'::'.$originalSlug);
            $this->delete($existing->term());
        }

        // The "old" state shouldn't be maintained within the Stache, otherwise it'll be there
        // when the term is retrieved again. Ideally this should be done in a more generic
        // location. We'll also use a clone to avoid modifying the original instance.
        $term = clone $term;
        $term->syncOriginal();

        $this->writeItemToDisk($term);

        $this->forgetItemsForSlug($term->inDefaultLocale()->slug());

        foreach ($term->localizations() as $item) {
            $key = $this->getItemKey($item);

            $this->setPath($key, $item->path());

            $this->resolveIndexes()->each->updateItem($item);

            $this->cacheItem($item);
        }
    }

    private function forgetItemsForSlug(string $slug): void
    {
        $this->paths()->keys()
            ->merge($this->index('title')->keys())
            ->filter(fn ($key) => Str::after((string) $key, '::') === $slug)
            ->unique()
            ->each(fn ($key) => $this->forgetItem($key));
    }

    public function delete($term)
    {
        $this->deleteItemFromDisk($term);

        foreach ($term->localizations() as $item) {
            $key = $this->getItemKey($item);

            $this->forgetItem($key);

            $this->forgetPath($key);

            $this->resolveIndexes()->filter->isCached()->each->forgetItem($key);
        }
    }

    protected function getItemFromModifiedPath($path)
    {
        return parent::getItemFromModifiedPath($path)->localizations()->all();
    }
}
