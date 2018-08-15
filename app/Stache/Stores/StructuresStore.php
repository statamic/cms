<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\Stache\Stache;
use Illuminate\Filesystem\Filesystem;
use Statamic\Contracts\Data\Structures\Structure;

class StructuresStore extends BasicStore
{
    protected $entryUris;

    public function __construct(Stache $stache, Filesystem $files)
    {
        parent::__construct($stache, $files);

        $this->entryUris = collect();
        $this->forEachSite(function ($site) {
            $this->entryUris->put($site, collect());
        });
    }

    public function key()
    {
        return 'structures';
    }

    public function getItemsFromCache($cache)
    {
        return $cache->map(function ($item, $handle) {
            return app(Structure::class)
                ->handle($handle)
                ->data($item);
        });
    }

    public function createItemFromFile($path, $contents)
    {
        return app(Structure::class)
            ->handle(pathinfo($path, PATHINFO_FILENAME))
            ->data(YAML::parse($contents));
    }

    public function getItemKey($item, $path)
    {
        return pathinfo($path)['filename'];
    }

    public function filter($file)
    {
        $relative = $file->getPathname();

        $dir = str_finish($this->directory, '/');

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }

    public function save(Structure $structure)
    {
        $path = $this->directory . '/' . $structure->handle() . '.yaml';
        $contents = YAML::dump($this->toSaveableArray($structure));

        $this->files->put($path, $contents);
    }

    protected function toSaveableArray($structure)
    {
        $data = $structure->data();

        $data['tree'] = $this->removeEmptyChildren($data['tree']);

        return $data;
    }

    protected function removeEmptyChildren($array)
    {
        return collect($array)->map(function ($item) {
            $item['children'] = $this->removeEmptyChildren(array_get($item, 'children', []));

            if (empty($item['children'])) {
                unset($item['children']);
            }

            return $item;
        })->all();
    }

    public function getKeyFromUri(string $uri): ?string
    {
        if ($key = collect($this->entryUris->first())->flip()->get($uri)) {
            return $key;
        }

        return null;
    }

    public function getCacheableMeta()
    {
        return array_merge(parent::getCacheableMeta(), [
            'entryUris' => $this->entryUris->toArray()
        ]);
    }

    public function loadMeta($data)
    {
        parent::loadMeta($data);

        $this->setEntryUris($data['entryUris']);
    }

    public function setEntryUris($uris)
    {
        $this->entryUris = collect($uris);
    }

    public function getEntryUris($site = null)
    {
        $site = $site ?? $this->stache->sites()->first();

        return $this->entryUris->get($site);
    }

    public function setItem($key, $item)
    {
        parent::setItem($key, $item);

        $this->flushStructureEntryUris($key);

        foreach ($this->stache->sites() as $site) {
            foreach ($item->uris() as $key => $uri) {
                $this->entryUris->get($site)->put($item->handle() . '::' . $key, $uri);
            }
        }

        return $this;
    }

    public function removeItem($key)
    {
        parent::removeItem($key);

        $this->flushStructureEntryUris($key);

        return $this;
    }

    protected function flushStructureEntryUris($handle)
    {
        foreach ($this->stache->sites() as $site) {
            $this->entryUris->put($site, collect($this->entryUris->get($site))->reject(function ($uri, $key) use ($handle) {
                return str_before($key, '::') === $handle;
            }));
        }
    }
}
