<?php

namespace Statamic\Stache\Stores;

use Statamic\Stache\Fakes\YAML;

class CollectionsStore extends BasicStore
{
    public function key()
    {
        return 'collections';
    }

    public function createItemFromFile($path, $contents)
    {
        $id = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);

        // @TODO: Change this to Collection::create() etc once brought into Statamic.
        $fakeCollection = new class($id, $data) {
            protected $data;
            protected $id;
            function __construct($id, $data) {
                $this->id = $id;
                $this->data = $data;
            }
            public function id() { return $this->id; }
            public function uri() { return null; }
            public function toCacheableArray() { }
        };

        return $fakeCollection;
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
}
