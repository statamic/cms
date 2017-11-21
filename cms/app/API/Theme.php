<?php

namespace Statamic\API;

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
        $path = 'settings/macros.yaml';

        $macros = array_reindex(YAML::parse(File::disk('theme')->get($path)));

        return array_get($macros, $macro);
    }
}
