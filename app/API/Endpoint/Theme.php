<?php

namespace Statamic\API\Endpoint;

/**
 * Interacting with the theme
 */
class Theme
{
    /**
     * Get a macro!
     *
     * @param string  $macro  Name of the modifier
     * @return array
     */
    public static function getMacro($macro)
    {
        $path = base_path('resources/macros.yaml');

        $macros = array_reindex(YAML::parse(File::get($path)));

        return array_get($macros, $macro);
    }
}
