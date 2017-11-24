<?php

namespace Statamic\Stache;

use Statamic\API\Str;
use Illuminate\Support\Collection;

class AggregateRepository
{
    /**
     * @var Collection
     */
    protected $repositories;

    /**
     * @var string
     */
    private $key;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->repositories = collect();
        $this->key = $key;
    }

    /**
     * Get a repository
     *
     * @param string $key
     * @return Repository
     */
    public function repo($key)
    {
        $this->ensureRepo($key);

        return $this->repositories->get($key);
    }

    /**
     * Remove a repository
     *
     * @param string $key
     * @return $this
     */
    public function removeRepo($key)
    {
        $this->repositories->forget($key);

        return $this;
    }

    /**
     * Get the key
     *
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Get all the repositories
     *
     * @return Collection
     */
    public function repos()
    {
        return $this->repositories;
    }

    /**
     * Get all the paths of all repos, grouped by repo key
     *
     * @return Collection
     */
    public function getPaths()
    {
        return $this->repositories->map(function ($repo) {
            return $repo->getPaths();
        });
    }

    /**
     * Get all the paths for all repos, in all locales
     *
     * @return Collection
     */
    public function getPathsForAllLocales()
    {
        return $this->repositories->map(function (Repository $repo) {
            return $repo->getPathsForAllLocales();
        });
    }

    /**
     * Get all the URIs of all repos, grouped by repo key
     *
     * @param string|null $locale
     * @return Collection
     */
    public function getUris($locale = null)
    {
        return $this->repositories->map(function ($repo) use ($locale) {
            return $repo->getUris($locale);
        });
    }

    /**
     * Get all the URIs for all repos, in all locales
     *
     * @return Collection
     */
    public function getUrisForAllLocales()
    {
        return $this->repositories->map(function (Repository $repo) {
            return $repo->getUrisForAllLocales();
        });
    }

    /**
     * Get all the items of all repos, grouped by repo key
     *
     * @return Collection
     */
    public function getItems()
    {
        return $this->repositories->map(function ($repo) {
            return $repo->getItems();
        });
    }

    /**
     * Get a path from a repo
     *
     * @param string $id A `repo::id` string
     * @return string
     */
    public function getPath($id)
    {
        list($repo, $id) = $this->extractKeys($id);

        return $this->repo($repo)->getPath($id);
    }

    /**
     * Get a URI from a repo
     *
     * @param string $id A `repo::id` string
     * @return string
     */
    public function getUri($id)
    {
        list($repo, $id) = $this->extractKeys($id);

        return $this->repo($repo)->getUri($id);
    }

    /**
     * Get an item from a repo
     *
     * @param string $id A `repo::id` string
     * @return mixed
     */
    public function getItem($id)
    {
        list($repo, $id) = $this->extractKeys($id);

        return $this->repo($repo)->getItem($id);
    }

    /**
     * Set a path on a repo
     *
     * @param string $id A `repo::id` string
     * @param string $path
     * @param string|null $locale
     * @return $this
     */
    public function setPath($id, $path, $locale = null)
    {
        list($repo, $id) = $this->extractKeys($id);

        $this->ensureRepo($repo);

        $this->repo($repo)->setPath($id, $path, $locale);

        return $this;
    }

    /**
     * Set all the paths for all repos, in all locales
     *
     * @return $this
     */
    public function setPathsForAllLocales($paths)
    {
        collect($paths)->each(function ($paths, $locale) {
            collect($paths)->each(function ($path, $id) use ($locale) {
                $this->setPath($id, $path, $locale);
            });
        });

        return $this;
    }

    /**
     * Set all the URIs for all repos, in all locales
     *
     * @return $this
     */
    public function setUrisForAllLocales($paths)
    {
        collect($paths)->each(function ($uris, $locale) {
            collect($uris)->each(function ($uri, $id) use ($locale) {
                $this->setUri($id, $uri, $locale);
            });
        });

        return $this;
    }

    /**
     * Set a URI on a repo
     *
     * @param string $id A `repo::id` string
     * @param string $url
     * @param string|null $locale
     * @return $this
     */
    public function setUri($id, $url, $locale = null)
    {
        list($repo, $id) = $this->extractKeys($id);

        $this->ensureRepo($repo);

        $this->repo($repo)->setUri($id, $url, $locale);

        return $this;
    }

    /**
     * Set an item on a repo
     *
     * @param string $id A `repo::id` string
     * @param mixed  $item
     * @return $this
     */
    public function setItem($id, $item)
    {
        list($repo, $id) = $this->extractKeys($id);

        $this->ensureRepo($repo);

        $this->repo($repo)->setItem($id, $item);

        return $this;
    }

    /**
     * Set multiple paths
     *
     * @param array $paths
     * @return $this
     */
    public function setPaths($paths)
    {
        collect($paths)->each(function ($path, $key) {
            $this->setPath($key, $path);
        });

        return $this;
    }

    /**
     * Set multiple URIs
     *
     * @param array $uris
     * @return $this
     */
    public function setUris($uris)
    {
        collect($uris)->each(function ($path, $key) {
            $this->setUri($key, $path);
        });

        return $this;
    }

    /**
     * Set multiple items
     *
     * @param array $items
     * @return $this
     */
    public function setItems($items)
    {
        collect($items)->each(function ($item, $key) {
            $this->setItem($key, $item);
        });

        return $this;
    }

    /**
     * Remove an item
     *
     * @param string $id
     * @return $this
     */
    public function removeItem($id)
    {
        list($key, $id) = $this->extractKeys($id);

        $this->repo($key)->removeItem($id);

        return $this;
    }

    /**
     * Get all the IDs from all repos
     *
     * @return Collection
     */
    public function getIds()
    {
        return $this->repos()->flatMap(function (Repository $repo) {
            return $repo->getIds();
        });
    }

    /**
     * Get the key of a repo where a given ID is located
     *
     * @param string $id
     * @return string
     */
    public function getRepoKeyById($id)
    {
        $ids = collect();

        $this->repos()->each(function (Repository $repo) use (&$ids) {
            $ids->put($repo->key(), $repo->getIds()->all());
        });

        $ids = $ids->flatMap(function ($ids, $key) {
            $new = [];
            foreach ($ids as $id) {
                $new[$id] = $key;
            }
            return $new;
        });

        return $ids->get($id);
    }

    /**
     * Get the repo where a given ID is located
     *
     * @param string $id
     * @return \Statamic\Stache\Repository
     */
    public function getRepoById($id)
    {
        $key = $this->getRepoKeyById($id);

        return $this->repo($key);
    }

    /**
     * Get the ID of a repo by path
     *
     * @param string $path
     * @return string
     */
    public function getIdByPath($path)
    {
        list($key, $path) = $this->extractKeys($path);

        return $this->repo($key)->getIdByPath($path);
    }

    /**
     * Make sure there's a repository at the given key
     *
     * @param string $key
     */
    public function ensureRepo($key)
    {
        if (! $this->repositories->has($key)) {
            $this->repositories->put($key, new Repository($key, $this->key.'/'.$key));
        }
    }

    /**
     * Take a string and extract the repo key and the id
     *
     * @param string $str
     * @return array
     * @throws \Exception
     */
    protected function extractKeys($str)
    {
        if (! Str::contains($str, '::')) {
            throw new \Exception(
                sprintf('No separator (::) present in string [%s] when trying to extract repo keys.', $str)
            );
        }

        return explode('::', $str);
    }
}
