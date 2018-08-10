<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\Contracts\Data\Structures\Structure;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class StructuresStore extends BasicStore
{
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

    public function save(StructureContract $structure)
    {
        $path = $this->directory . '/' . $structure->handle() . '.yaml';
        $contents = YAML::dump($structure->data());

        $this->files->put($path, $contents);
    }
}
