<?php

namespace Statamic\API\Endpoint;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Helper;
use Statamic\API\Folder;
use Statamic\Fields\FieldsetLoader;
use Statamic\Exceptions\FileNotFoundException;

class Fieldset
{
    /**
     * A simple fieldset cache
     *
     * @var array
     */
    private static $fieldsets = [];

    /**
     * Create a fieldset
     *
     * @param string $name
     * @param array  $contents
     * @return \Statamic\Contracts\CP\Fieldset
     */
    public function create($name, $contents = [])
    {
        /** @var \Statamic\Contracts\CP\Fieldset $fieldset */
        $fieldset = app('Statamic\Contracts\CP\Fieldset');

        $fieldset->name($name);
        $fieldset->contents($contents);

        return $fieldset;
    }

    /**
     * Get a fieldset
     *
     * @param string $name
     * @param string $type
     * @return \Statamic\CP\Fieldset
     * @throws FileNotFoundException
     */
    public function get($name, $type = 'default')
    {
        $names = Helper::ensureArray($name);

        foreach ($names as $name) {
            try {
                return self::fetch($name, $type);
            } catch (FileNotFoundException $e) {
                continue;
            }
        }

        throw new FileNotFoundException('Fieldset(s) not found: ['.join(', ', $names).']');
    }

    /**
     * Get a single fieldset
     *
     * @param string $name
     * @param string $type
     * @return \Statamic\CP\Fieldset
     * @throws FileNotFoundException
     */
    private function fetch($name, $type = 'default')
    {
        return app(FieldsetLoader::class)->load($name);
    }

    public function exists($name, $type = 'default')
    {
        $fieldset = self::create($name);

        $fieldset->type($type);

        return File::exists($fieldset->path());
    }

    /**
     * Get all the fieldsets
     *
     * @param string $type
     * @return \Statamic\Contracts\CP\Fieldset[]
     */
    public function all($type = 'default')
    {
        $fieldsets = [];
        $files = collect_files(Folder::getFiles(resource_path('fieldsets')))->removeHidden()->all();

        foreach ($files as $path) {
            $filename = pathinfo($path)['filename'];
            $fieldsets[] = self::get($filename);
        }

        return $fieldsets;
    }
}
