<?php

namespace Statamic\Stache;

use Illuminate\Support\Collection;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Config;
use Statamic\Stache\Drivers\AggregateDriver;
use Statamic\Stache\Staches\TaxonomyStache;

class Stache
{
    /**
     * @var Collection
     */
    private $drivers;

    /**
     * @var Collection
     */
    private $repositories;

    /**
     * @var array
     */
    private $locales;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var array|null
     */
    protected $config;

    /**
     * @var array
     */
    private $updates = [];

    /**
     * @var string
     */
    public $building_path;

    /**
     * The "temperature" of the Stache.
     *
     * @var int
     */
    private $temperature;

    /**
     * When the Stache is cold
     *
     * @var int
     */
    const TEMP_COLD = 0;

    /**
     * When the cache is warm
     *
     * @var int
     */
    const TEMP_WARM = 1;

    public $taxonomies;

    /**
     * Stache constructor.
     *
     * @param TaxonomyStache $taxonomyStache
     */
    public function __construct(TaxonomyStache $taxonomyStache)
    {
        $this->taxonomies = $taxonomyStache;

        $this->drivers = collect();
        $this->repositories = collect();

        $this->building_path = cache_path('stache_building');

        $this->temperature = self::TEMP_COLD;
    }

    /**
     * Change the temperature to cold
     *
     * @return void
     */
    public function cool()
    {
        $this->temperature = self::TEMP_COLD;

        if (! File::exists($this->building_path)) {
            File::put($this->building_path, true);
        }
    }

    /**
     * Change the temperature to warm
     *
     * @return void
     */
    public function heat()
    {
        $this->temperature = self::TEMP_WARM;

        if (File::exists($this->building_path)) {
            File::delete($this->building_path);
        }
    }

    /**
     * Whether the Stache is warm (contains at least the initial data)
     *
     * @return bool
     */
    public function isWarm()
    {
        return $this->temperature === self::TEMP_WARM;
    }

    /**
     * Whether the Stache is cold (doesn't yet contain at least the initial data)
     *
     * @return bool
     */
    public function isCold()
    {
        return $this->temperature === self::TEMP_COLD;
    }

    /**
     * Whether the Stache is in the middle of performing its initial warm up.
     *
     * @return bool
     */
    public function isPerformingInitialWarmUp()
    {
        return File::exists($this->building_path);
    }

    /**
     * Set the locales used in the Stache
     *
     * @param array $locales
     */
    public function locales($locales)
    {
        $this->locales = $locales;
    }

    /**
     * Register a driver
     *
     * @param \Statamic\Stache\Driver $driver
     */
    public function registerDriver(Driver $driver)
    {
        $key = $driver->key();

        $this->drivers->put($key, $driver);

        $repo = ($driver instanceof AggregateDriver)
            ? new AggregateRepository($key, $driver->getRepoCacheKey())
            : new Repository($key, $driver->getRepoCacheKey());

        $this->repositories->put($key, $repo);
    }

    /**
     * Get a driver
     *
     * @param string $key
     * @return Driver
     */
    public function driver($key)
    {
        return $this->drivers->get($key);
    }

    /**
     * Get all registered drivers
     *
     * @return \Illuminate\Support\Collection
     */
    public function drivers()
    {
        return $this->drivers;
    }

    /**
     * Get a repository
     *
     * @param string $key
     * @return Repository
     */
    public function repo($key)
    {
        // If the repo key contains a ::, it means it's a namespaced key
        // with the intention of getting an aggregate repo's nested repo.
        if (Str::contains($key, '::')) {
            list($parent, $child) = explode('::', $key);
            return $this->repositories->get($parent)->repo($child);
        }

        return $this->repositories->get($key);
    }

    /**
     * Get all repositories
     *
     * @return \Illuminate\Support\Collection
     */
    public function repos()
    {
        return $this->repositories;
    }

    /**
     * Get or set the meta data
     *
     * @param array|null $meta
     * @return null|array
     */
    public function meta($meta = null)
    {
        if (is_null($meta)) {
            return $this->meta;
        }

        $this->meta = $meta;
    }

    /**
     * Get or set the config
     *
     * @param null|array $config
     * @return array|null
     */
    public function config($config = null)
    {
        if (is_null($config)) {
            return $this->config;
        }

        $this->config = $config;
    }

    /**
     * Get all relatable items
     *
     * @return Collection
     */
    public function all()
    {
        return $this->repos()->reduce(function ($all, $repo) {
            if (! $this->driver($repo->key())->isRelatable()) {
                return $all;
            }

            $items = $repo->getItems();

            if ($repo instanceof AggregateRepository) {
                $items = $items->flatMap(function ($items) {
                    return $items;
                });
            }

            return $all->merge($items);
        }, collect());
    }

    /**
     * Get all the IDs (of relatable items)
     *
     * They will use the IDs as keys and paths as values. The paths will be namespaced by their
     * repo keys (and sub-repo keys if they are aggregates). This will prevent IDs being merged
     * into oblivion if paths are used as keys and there are similar paths between repos.
     *
     * @return Collection
     */
    public function ids()
    {
        $ids = [];

        $this->repos()->each(function ($repo) use (&$ids) {
            $driver = $this->driver($repo->key());

            // Relatable items don't have IDs that are used for fetching.
            if (! $driver->isRelatable()) {
                return;
            }

            if ($repo instanceof AggregateRepository) {
                $paths = [];
                foreach ($repo->getPaths() as $key => $repo_paths) {
                    foreach ($repo_paths as $id => $path) {
                        $paths[$id] = $repo->key() . '/' . $key . '::' . $path;
                    }
                }
            } else {
                $paths = $repo->getPaths()->map(function ($path) use ($repo) {
                    return $repo->key() . '::' . $path;
                })->all();
            }

            $ids = $ids + $paths;
        });

        return collect($ids);
    }

    /**
     * Get all the URLs across all locales
     *
     * @return Collection
     */
    public function uris()
    {
        $uris = [];

        $this->repos()->each(function ($repo) use (&$uris) {
            if (! $this->driver($repo->key())->isLocalizable()) {
                return;
            }

            foreach ($this->locales as $locale) {
                if ($repo instanceof AggregateRepository) {
                    foreach ($repo->getUris($locale) as $key => $repo_uris) {
                        foreach ($repo_uris as $id => $uri) {
                            $uris[$locale . '::' . $uri] = $id;
                        }
                    }
                } else {
                    foreach ($repo->getUris($locale) as $id => $uri) {
                        $uris[$locale . '::' . $uri] = $id;
                    }
                }
            }
        });

        return collect($uris);
    }

    public function updated($key = null)
    {
        if (is_null($key)) {
            return collect($this->updates);
        }

        $this->updates[] = $key;
    }

    /**
     * Build up a config array
     *
     * Used for comparisons to see if the config has changed between requests.
     *
     * @return array
     */
    public function buildConfig()
    {
        $meta = [
            'version' => STATAMIC_VERSION
        ];

        $config = Config::all();

        return compact('meta', 'config');
    }

    /**
     * Get all the file paths with duplicate IDs.
     *
     * @return Collection
     */
    public function duplicates()
    {
        $cached = \Cache::get('stache::duplicates', []);

        return collect($cached)->map(function ($paths, $id) {
            $item = $this->ids()->get($id);
            $path = explode('::', $item)[1];
            array_unshift($paths, $path);
            return $paths;
        });
    }
}
