<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\API\Taxonomy;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as TaxonomyContract;

class TaxonomiesStore extends BasicStore
{
    public function key()
    {
        return 'taxonomies';
    }

    public function createItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);

        return Taxonomy::make($handle)
            ->title(array_get($data, 'title'));
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

    public function save(TaxonomyContract $taxonomy)
    {
        $path = $this->directory . '/' . $taxonomy->path() . '.yaml';
        $contents = YAML::dump($taxonomy->data());

        $this->files->put($path, $contents);
    }
}
