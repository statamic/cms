<?php

namespace Statamic\Fields;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Exceptions\FileNotFoundException;

class FieldsetLoader
{
    private static $fieldsets = [];

    public function load($handle)
    {
        // TODO: temp
        $name = $handle;
        $type = 'default';

        $fieldset = \Statamic\API\Fieldset::create($handle);

        $fieldset->type($type);

        // Retrieve from the cache if available
        if ($cached = array_get(self::$fieldsets, $type.'.'.$name)) {
            return $cached;
        }

        // First check the user's fieldset path
        $path = $fieldset->path();
        if (! File::exists($path)) {
            // Then the default fallbacks
            $path = statamic_path("defaults/fieldsets/{$name}.yaml");

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
}
