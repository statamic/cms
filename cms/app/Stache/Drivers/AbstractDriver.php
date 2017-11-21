<?php

namespace Statamic\Stache\Drivers;

use Illuminate\Support\Collection;
use Statamic\API\Config;
use Statamic\API\Folder;
use Statamic\Stache\AggregateRepository;
use Statamic\Stache\Stache;
use Statamic\Stache\Driver;
use Statamic\Stache\Repository;
use Statamic\Exceptions\DuplicateIdException;

abstract class AbstractDriver implements Driver
{
    /**
     * @var \Statamic\Stache\Stache
     */
    protected $stache;

    /**
     * @var bool
     */
    protected $relatable = true;

    /**
     * @var bool
     */
    protected $localizable = false;

    /**
     * @var bool
     */
    protected $traverse_recursively = true;

    /**
     * @var bool
     */
    protected $routable = false;

    /**
     * @var bool
     */
    protected $multi_item = false;

    /**
     * AbstractDriver constructor.
     *
     * @param \Statamic\Stache\Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
    }

    /**
     * Get the identifying key
     *
     * @return string
     */
    public function key()
    {
        preg_match('/\\\(\w*)Driver$/', get_called_class(), $matches);

        return strtolower($matches[1]);
    }

    /**
     * Get the key used for caching
     *
     * @return string
     */
    public function getRepoCacheKey()
    {
        return $this->key();
    }

    /**
     * Get the Flysystem driver
     *
     * @return \League\Flysystem\Filesystem
     */
    public function getFilesystemDriver()
    {
        return Folder::disk('content')->filesystem()->getDriver();
    }

    /**
     * Get the root folder within the filesystem to search for files
     *
     * @return string
     */
    abstract public function getFilesystemRoot();

    /**
     * Get a collection of items based on any modified files
     *
     * @param Collection $modified  The modified files and their raw contents.
     * @return Collection           A collection of arrays with item (the object), and the file path.
     */
    public function getItems(Collection $modified)
    {
        return $modified->map(function ($contents, $path) {
            if (! $item = $this->createItem($path, $contents)) {
                return;
            }

            return compact('item', 'path');
        })->filter();
    }

    /**
     * Create the item object
     *
     * @param string $path      Path to file
     * @param string $contents  Raw contents of the file
     * @return mixed
     */
    abstract public function createItem($path, $contents);

    /**
     * Delete the items from the repo
     *
     * @param \Statamic\Stache\Repository $repo
     * @param Collection $deleted
     * @param Collection $modified
     */
    public function deleteItems($repo, $deleted, $modified)
    {
        $deleted->each(function ($path) use ($repo) {
            $repo->removeItem($repo->getIdByPath($path));
        });
    }

    /**
     * Whether this driver stores items that can be used in relationships
     *
     * In other words, do they have IDs?
     *
     * @return bool
     */
    public function isRelatable()
    {
        return $this->relatable;
    }

    /**
     * Whether this driver stores items that can be localized
     *
     * @return bool
     */
    public function isLocalizable()
    {
        return $this->localizable;
    }

    /**
     * Whether this driver stores items that can have URLs
     *
     * @return bool
     */
    public function isRoutable()
    {
        return $this->routable;
    }

    /**
     * Whether this driver stores multiple items per file (like assets in a folder.yaml)
     *
     * @return bool
     */
    public function isMultiItem()
    {
        return $this->multi_item;
    }

    /**
     * Whether this driver's filesystem should be traversed recursively.
     *
     * @return bool
     */
    public function traverseRecursively()
    {
        return $this->traverse_recursively;
    }

    /**
     * Get the ID of the item
     *
     * @param mixed  $item  An item instance
     * @param string $path  The path to the file
     * @return mixed
     */
    public function getItemId($item, $path)
    {
        return $item->id();
    }

    /**
     * Make sure duplicate IDs are detected
     *
     * @param $item
     * @return array
     */
    public function ensureUniqueId($item)
    {
        if (! Config::get('system.ensure_unique_ids')) {
            return;
        }

        $id = $item->id();
        $path = $item->path();

        // If it doesn't have an ID yet, one will be generated,
        // for it so we can safely assume it will be unique.
        if (! $id) {
            return;
        }

        if (! $existing = $this->stache->ids()->get($id)) {
            return;
        }

        // The paths are namespaced with the repo keys. We'll grab the repo key and file path.
        list($existing_repo_key, $existing_path) = explode('::', $existing);

        // If it's the same path, we're updating the item. It's meant to be the same ID!
        if ($path === $existing_path) {
            return;
        }

        throw new DuplicateIdException($path, $existing_path, $existing_repo_key, $id);
    }

    /**
     * Whether a given file should be picked up by the traverser
     *
     * @param array $file  A Flysystem file
     * @return boolean
     */
    abstract public function isMatchingFile($file);

    /**
     * Get the locale based on the path
     *
     * @param string $path
     * @return string
     */
    abstract public function getLocaleFromPath($path);

    /**
     * Get the localized URI
     *
     * @param        $locale
     * @param array  $data
     * @param string $path
     * @return string
     */
    abstract public function getLocalizedUri($locale, $data, $path);

    /**
     * The array to be persisted to cache
     *
     * @param Repository|AggregateRepository $repo
     * @return array
     */
    abstract public function toPersistentArray($repo);

    /**
     * Take the persisted items from the cache and return a collection of objects
     *
     * @param Collection $collection
     * @return Collection
     */
    public function load($collection)
    {
        return $collection;
    }
}
