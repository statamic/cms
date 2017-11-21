<?php

namespace Statamic\Stache;

use Exception;
use Illuminate\Support\Collection;
use Statamic\API\Config;
use Statamic\API\Helper;
use Statamic\API\URL;
use Statamic\API\YAML;
use Statamic\Events\DataIdCreated;
use Statamic\Events\StacheItemInserted;
use Illuminate\Support\Facades\Log;
use Statamic\Stache\Drivers\AbstractDriver;
use Statamic\Stache\Drivers\AggregateDriver;
use Statamic\Exceptions\DuplicateIdException;

class Updater
{
    /**
     * @var \Statamic\Stache\Stache
     */
    private $stache;

    /**
     * @var AbstractDriver
     */
    private $driver;

    /**
     * @var Repository|AggregateRepository
     */
    private $repo;

    /**
     * @var Collection
     */
    private $modified;

    /**
     * @var Collection
     */
    private $deleted;

    /**
     * @var bool
     */
    protected $updates;

    /**
     * @var bool
     */
    protected $localized;

    /**
     * @param \Statamic\Stache\Stache $stache
     */
    public function __construct(Stache $stache, $driver)
    {
        $this->stache = $stache;
        $this->driver = $driver;
        $this->repo = $stache->repo($this->driver->key());

        $this->modified = collect();
        $this->deleted = collect();

        $this->localized = count(Config::getLocales()) > 1;
    }

    /**
     * Set modified files
     *
     * @param array $modified
     * @return $this
     */
    public function modified(array $modified)
    {
        $this->modified = $this->sortDeepest($modified);

        return $this;
    }

    /**
     * Set deleted files
     *
     * @param array $deleted
     * @return $this
     */
    public function deleted(array $deleted)
    {
        $this->deleted = $this->sortDeepest($deleted);

        return $this;
    }

    /**
     * Perform the update
     *
     * @return bool
     */
    public function update()
    {
        // Track whether any updates have occurred.
        $this->updates = ! $this->modified->isEmpty() || ! $this->deleted->isEmpty();

        // Group the files by their locales
        $this->prepareFiles();

        // Update the default locale. This will create objects and place them in the Stache.
        $this->updateDefault();

        // Localized data is stored on the existing objects in the Stache. We'll need to
        // update existing objects already in the Stache instead of creating new ones.
        $this->updateLocalized();

        // Update the URIs for any localized pages
        $this->updateLocalizedPageUris();

        // Update the URIs for any localized entries
        $this->updateLocalizedEntryUris();

        // If any modifications or deletions occurred, the Stache'll need
        // to be re-persisted. Persisting the cache means more overhead
        // so if we can avoid it that would mean a performance boost.
        return $this->updates;
    }

    /**
     * Update default locale
     *
     * @return void
     */
    private function updateDefault()
    {
        $this->driver->deleteItems(
            $this->repo,
            $this->deleted->get(default_locale()),
            $this->modified->get(default_locale())
        );

        if ($this->driver instanceof AggregateDriver) {
            $this->updateAggregate();
        } else {
            $this->repo->load();
            $this->updateItems($this->driver->getItems($this->modified->get(default_locale())));
        }
    }

    /**
     * Update any localized versions
     *
     * @throws \Exception
     */
    private function updateLocalized()
    {
        if (!$this->localized || !$this->driver->isLocalizable()) {
            return;
        }

        foreach (Config::getOtherLocales() as $locale) {
            if ($this->driver instanceof AggregateDriver) {
                $this->updateLocaleAggregate($locale, $this->repo, $this->modified->get($locale), $this->deleted->get($locale));
            } else {
                $this->updateLocale($locale, $this->repo, $this->modified->get($locale), $this->deleted->get($locale));
            }
        }
    }

    private function updateLocaleAggregate($locale, $repo, $modified, $deleted)
    {
        foreach ($repo->repos() as $repo) {
            // Using map because filter doesnt pass keys in Laravel 5.1
            $modifiedFiles = $modified->map(function ($contents, $path) use ($repo) {
                $key = $this->driver->getKeyFromPath($path);
                return ($key === $repo->key()) ? $contents : false;
            })->filter();

            $deletedFiles = $deleted->filter(function ($path) use ($repo) {
                return $this->driver->getKeyFromPath($path) === $repo->key();
            })->filter();

            $this->updateLocale($locale, $repo, $modifiedFiles, $deletedFiles);
        }
    }

    /**
     * Update a specific locale
     *
     * @param string $locale
     * @param Repository $repo
     * @param Collection $modified
     * @param Collection $deleted
     */
    private function updateLocale($locale, $repo, $modified, $deleted)
    {
        if ($modified->isEmpty() && $deleted->isEmpty()) {
            return;
        }

        $repo->load();

        $deleted->each(function ($path) use ($locale, $repo) {
            $id = $repo->getIdByPath($path, $locale);

            // Remove the locale if the item still exists. If the default
            // locale was deleted, the item won't exist at this point.
            if ($item = $repo->getItem($id)) {
                $item->removeLocale($locale);
                $item->in(default_locale())->syncOriginal();
                $repo->setItem($id, $item);
            }

            $repo->removePath($id, $locale);

            if ($this->driver->isRoutable()) {
                $repo->removeUri($id, $locale);
            }
        });

        $modified->each(function ($contents, $path) use ($locale, $repo) {
            $data = YAML::parse($contents);

            if (! $id = array_get($data, 'id')) {
                throw new \Exception(
                    "The localized item located at [$path] is missing an `id` 
                    value which should match the ID from the default locale."
                );
            }

            $item = $repo->getItem($id);

            $item->in($locale)->data($data);
            $item->syncOriginal();

            $repo->setPath($id, $path, $locale);
            $repo->setItem($id, $item);

            if ($this->driver->isRoutable()) {
                $repo->setUri($id, $this->driver->getLocalizedUri($locale, $data, $path), $locale);
            }
        });
    }

    private function updateItems($items, $repo_prefix = null)
    {
        $items->each(function ($data) use ($repo_prefix, $items) {
            $item = $data['item'];
            $path = $data['path'];

            try {
                $item = $this->ensureId($item);
            } catch (DuplicateIdException $e) {
                return;
            }

            $id = $this->driver->getItemId($item, $path);
            if ($repo_prefix) {
                $id = $repo_prefix . '::' . $id;
            }

            $this->repo->setPath($id, $path)
                       ->setItem($id, $item);

            $this->setUriForItem($id, $item);
        });
    }

    private function updateAggregate()
    {
        $this->driver->getModifiedItems($this->modified)->each(function ($items, $key) {
            $this->repo->ensureRepo($key);
            $repo = $this->repo->repo($key);

            // Make sure all the items already exist, since we'll override any updated items.
            // For a repo like entries, we will only be adding/updating individual entries.
            $repo->load();

            $this->updateItems($items, $key);

            // For an aggregrate repo like Assets, where there are many items located in a single file,
            // the file wasn't actually deleted so it won't get picked up in the 'delete' step. So,
            // we'll need to remove any deleted items here, in the update step.
            if ($this->driver->isMultiItem()) {
                // Grab all the IDs, as keys, in preparation for the diff coming up.
                $keys = $items->keyBy(function ($item) {
                    return $item['item']->id();
                })->all();

                collect(
                    array_diff_key($repo->getPaths()->all(), $keys)
                )->keys()->each(function ($id) use ($repo) {
                    $repo->removeItem($id);
                });
            }
        });
    }

    private function setUriForItem($key, $item)
    {
        if (! method_exists($item, 'uri') || ! $this->driver->isRoutable()) {
            return;
        }

        // Items with a URI of `/` that aren't the homepage are just a
        // misconfigured item. That's fine, we just wont save a URI.
        if ($item->uri() === '/' && $item->slug() !== '') {
            return;
        }

        $this->repo->setUri($key, $item->uri());
    }

    /**
     * Sort an array by folder depth (amount of slashes)
     *
     * @param  array $arr  An array with paths for keys
     * @return Collection  The sorted collection
     */
    private function sortDeepest($arr)
    {
        return collect($arr)->sortBy(function ($contents, $path) {
            return substr_count($path, '/');
        });
    }

    /**
     * Make sure that the item has an ID
     *
     * @param mixed $item
     * @return mixed
     */
    protected function ensureId($item)
    {
        // If the item doesn't have a concept of an ID, don't worry.
        if (! method_exists($item, 'id')) {
            return $item;
        }

        $this->ensureUniqueId($item);

        if ($item->id()) {
            return $item;
        }

        $item->id(Helper::makeUuid());

        event(new DataIdCreated($item));

        return $item;
    }

    /**
     * Make sure duplicate IDs are caught early on
     *
     * @param mixed $item
     * @throws \Exception
     */
    protected function ensureUniqueId($item)
    {
        try {
            $this->driver->ensureUniqueId($item);
        } catch (DuplicateIdException $e) {
            $message = sprintf(
                'Cannot add [%s] to Stache repository [%s]. File at [%s] already exists in repository [%s] with an ID of [%s].',
                $e->getPath(),
                $this->repo->key(),
                $e->getExistingPath(),
                $e->getExistingRepo(),
                $e->getItemId()
            );
            $e->setMessage($message);

            Log::error($message);

            $this->cacheDuplicateId($e->getItemId(), $e->getPath());

            throw $e;
        }
    }

    /**
     * Save the duplicate ID to the Stache
     *
     * @param  string $id
     * @param  string $path
     * @return void
     */
    private function cacheDuplicateId($id, $path)
    {
        $key = 'stache::duplicates';
        $cache = collect(\Cache::get($key, []));
        $dupes = $cache->get($id, []);
        $dupes[] = $path;
        $cache->put($id, $dupes);
        \Cache::forever($key, $cache->all());
    }

    /**
     * Prepare files
     *
     * @return void
     */
    private function prepareFiles()
    {
        if ($this->localized && $this->driver->isLocalizable()) {
            $this->groupFilesByLocale();
        } else {
            $this->modified = collect([default_locale() => $this->modified]);
            $this->deleted  = collect([default_locale() => $this->deleted]);
        }

        // Ensure that each locale exists in the collection, at least as an empty state.
        foreach (Config::getLocales() as $locale) {
            $this->modified->put($locale, $this->modified->get($locale, collect()));
            $this->deleted->put($locale, $this->deleted->get($locale, collect()));
        }
    }

    /**
     * Group the files into locales
     *
     * @return void
     */
    protected function groupFilesByLocale()
    {
        $this->modified = $this->modified->groupBy(function ($contents, $path) {
            return $this->driver->getLocaleFromPath($path);
        }, true);

        $this->deleted = $this->deleted->groupBy(function ($path) {
            return $this->driver->getLocaleFromPath($path);
        }, true);
    }

    /**
     * Update the localized URIs for pages
     *
     * @return void
     */
    protected function updateLocalizedPageUris()
    {
        if (!$this->updates || !$this->localized || $this->driver->key() !== 'pages') {
            return;
        }

        foreach (Config::getOtherLocales() as $locale) {
            foreach ($this->repo->getUris($locale) as $id => $uri) {
                if ($parent = $this->repo->getItem($id)) {
                    // If the page still exists, we'll update all of it's child page URIs.
                    $this->updateChildPageUris(
                        $parent->in($locale)->get(),
                        $locale
                    );
                } else {
                    // If it doesn't exist, it would have been deleted so we'll just remove it.
                    $this->repo->removeUri($id, $locale);
                }
            }
        }
    }

    /**
     * Update the localized URIs for child pages for a given parent page
     *
     * @param \Statamic\Contracts\Data\Pages\Page $parent
     * @param string                              $locale
     */
    protected function updateChildPageUris($parent, $locale)
    {
        foreach ($parent->children(1) as $id => $child) {
            $child = $child->in($locale)->get();

            $uri = URL::assemble($parent->in($locale)->uri(), $child->slug());

            $this->repo->setUri($id, $uri, $locale);

            // Recursion!
            $this->updateChildPageUris($child, $locale);
        }
    }

    /**
     * Update the localized URIs for entries and terms
     *
     * When routes for specific locales have been defined for collections and taxonomies,
     * we need to add references to them even if no data has been localized.
     *
     * @return void
     */
    private function updateLocalizedEntryUris()
    {
        if (!$this->updates || !$this->localized || !in_array($this->driver->key(), ['entries', 'terms'])) {
            return;
        }

        foreach ($this->repo->repos() as $repo) {
            // Get the routes. If there's no route defined, we're done here.
            $section = ($this->driver->key() === 'entries') ? 'collections' : 'taxonomies';
            if (! $routes = Config::get("routes.{$section}.{$repo->key()}")) {
                continue;
            }

            // If the route definition is a string, there's no localization needed.
            if (is_string($routes)) {
                continue;
            }

            foreach (Config::getOtherLocales() as $locale) {
                // Get the route definition. If there isn't one, no localization needed.
                if (! $route = array_get($routes, $locale)) {
                    continue;
                }

                // If it's the same as the default route definition, no localization needed.
                if ($route === array_get($routes, default_locale())) {
                    continue;
                }

                foreach ($repo->getUris() as $id => $uri) {
                    // If the current locale already has a URI, it's done.
                    if ($repo->getUri($id, $locale)) {
                        continue;
                    }

                    // Add a localized uri for the given entry
                    $repo->setUri($id, $repo->getItem($id)->in($locale)->uri(), $locale);
                }
            }
        }
    }
}
