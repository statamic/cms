<?php

namespace Statamic\Data\Services;

use Statamic\API\File;
use Statamic\API\Folder;
use Statamic\API\Taxonomy;
use Statamic\API\YAML;

class TaxonomiesService
{
    public function all()
    {
        return app('stache')->repo('taxonomies')->getItems();
    }

    public function handle($handle)
    {
        if (! $this->exists($handle)) {
            return null;
        }

        $taxonomy = Taxonomy::create($handle);

        $taxonomy->data(
            YAML::parse($this->disk()->get($this->path($handle)))
        );

        return $taxonomy;
    }

    public function exists($handle)
    {
        return $this->all()->has($handle);
    }

    private function path($handle)
    {
        return "taxonomies/{$handle}.yaml";
    }

    /**
     * @param null $type
     * @return \Statamic\Filesystem\FileAccessor|\Statamic\Filesystem\FolderAccessor
     */
    private function disk($type = null)
    {
        return Folder::disk('content');
    }
}
