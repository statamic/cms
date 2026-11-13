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
use Symfony\Component\Finder\SplFileInfo;

class TaxonomyTermsStore extends ChildStore
{
    protected $valueIndex = Value::class;
    protected $storeIndexes = [
        'slug',
        'associations' => Indexes\Terms\Associations::class,
        'site',
    ];

    public function indexes($withUsages = true)
    {
        $indexes = parent::indexes($withUsages);

        // The other indexes read the associations, and would build them if they aren't
        // cached yet. Updating the associations first means they're only built once
        // when the store is warmed, rather than by the first index and again on update.
        return $indexes->only('associations')->merge($indexes->except('associations'));
    }

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
            // Only sync the original state when it's missing, since re-syncing would
            // discard changes made to a term being saved.
            if (empty($item->term()->getOriginal())) {
                $item->term()->syncOriginal();
            }

            return $item;
        }

        [$site, $slug] = explode('::', $key);

        if ($path = $this->getPath($key)) {
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

    public function sync($entry, $terms)
    {
        $taxonomy = $this->childKey();

        $terms = collect(Arr::wrap($terms))->mapWithKeys(function ($value) {
            return [Str::slug($value) => $value];
        });

        $indexes = $this->resolveIndexes()->except('associations');
        $associations = $this->index('associations');

        $rows = $terms->map(fn ($value, $slug) => [
            'value' => $value,
            'slug' => $slug,
            'entry' => $entry->id(),
            'collection' => $entry->collectionHandle(),
            'site' => $entry->locale(),
        ])->values()->all();

        // Most saves don't touch an entry's terms. When what's there for the entry is what would be
        // put there, the associations are left as they are rather than taken out, put back under
        // new keys and written again. The terms' own indexes are still updated, since what they
        // hold (how many published entries a term has, say) can change without the terms changing.
        //
        // A slug made of digits is a number here, having been an array key, and a string where the
        // associations were built from the entries, so slugs are compared as strings.
        $comparable = fn ($rows) => array_map(fn ($row) => ['slug' => (string) $row['slug']] + $row, $rows);

        if ($comparable($associations->forEntry($entry->id())) !== $comparable($rows)) {
            $associations->forgetEntry($entry->id());

            foreach ($rows as $row) {
                $associations->push($row);
            }

            $associations->cache();
        }

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

        Stache::cacheStore()->forever("stache::timestamps::{$this->key()}", $files->all());

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

        foreach ($term->localizations() as $item) {
            $key = $this->getItemKey($item);

            $this->forgetItem($key);

            $this->setPath($key, $item->path());

            $this->resolveIndexes()->each->updateItem($item);

            $this->cacheItem($item);
        }
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
