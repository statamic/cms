<?php

namespace Statamic\API;

use Statamic\API\Helper;
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
    public static function create($name, $contents = [])
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
    public static function get($name, $type = 'default')
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
    private static function fetch($name, $type = 'default')
    {
        $fieldset = self::create($name);

        $fieldset->type($type);

        // Retrieve from the cache if available
        if ($cached = array_get(self::$fieldsets, $type.'.'.$name)) {
            return $cached;
        }

        // First check the user's fieldset path
        $path = $fieldset->path();
        if (! File::exists($path)) {
            // Then the default fallbacks
            $path = "statamic/settings/defaults/fieldsets/{$name}.yaml";

            if (! File::exists($path)) {
                throw new FileNotFoundException("Fieldset [$name] doesn't exist.");
            }
        }

        $contents = YAML::parse(File::get($path));

        $fieldset->contents($contents);

        // Store in the cache
        self::$fieldsets[$type.'.'.$name] = $fieldset;

        return $fieldset;
    }

    public static function exists($name, $type = 'default')
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
    public static function all($type = 'default')
    {
        $fieldsets = [];
        $files = collect_files(Folder::getFiles(settings_path('fieldsets')))->removeHidden()->all();

        foreach ($files as $path) {
            $filename = pathinfo($path)['filename'];
            $fieldsets[] = self::get($filename);
        }

        return $fieldsets;
    }
}
