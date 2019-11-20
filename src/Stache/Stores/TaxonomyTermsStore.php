<?php

namespace Statamic\Stache\Stores;

use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\Term;
use Statamic\Facades\YAML;
use Statamic\Facades\Stache;
use Statamic\Facades\Taxonomy;
use Statamic\Stache\Indexes\Terms\Value;
use Statamic\Stache\Indexes\Terms\Titles;
use Symfony\Component\Finder\SplFileInfo;
use Statamic\Stache\Indexes\Terms\Associations;

class TaxonomyTermsStore extends ChildStore
{
    protected $valueIndex = Value::class;
    protected $storeIndexes = [
        'slug',
        'taxonomy',
        'associations' => Associations::class,
    ];

    public function getFileFilter(SplFileInfo $file) {
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
            ->slug(pathinfo(Path::clean($path), PATHINFO_FILENAME))
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
}
