<?php

namespace Statamic\Addons\Suggest;

use Statamic\API\Str;
use Statamic\Exceptions\ResourceNotFoundException;

class TypeMode
{
    public function resolve($mode, $customMode = null)
    {
        if ($mode === 'suggest') {
            $mode = $customMode;
        }

        // An addon may contain multiple modes. You may specify a "secondary" mode by delimiting with a dot.
        // For example, "bacon.bits" would reference the "BitsSuggestMode" in the "Bacon" addon.
        if (Str::contains($mode, '.')) {
            list($addon, $name) = explode('.', $mode);
        } else {
            $addon = $name = $mode;
        }

        $name = Str::studly($name);
        $addon = Str::studly($addon);
        $root = "Statamic\\Addons\\$addon";

        // First, native suggest modes.
        if (class_exists($native = 'Statamic\Addons\Suggest\Modes\\' . $name . 'Mode')) {
            return app($native);
        }

        // Suggest Modes may be stored in the root of the addon directory, named using YourAddonSuggestMode.php or
        // secondary ones may be named SecondarySuggestMode.php. Classes in the root will take precedence.
        if (class_exists($rootClass = "{$root}\\{$name}SuggestMode")) {
            return app($rootClass);
        }

        // Alternatively, Suggest Modes may be placed in a "SuggestModes" namespace.
        if (class_exists($namespacedClass = "{$root}\\SuggestModes\\{$name}SuggestMode")) {
            return app($namespacedClass);
        }

        throw new ResourceNotFoundException("Could not find files to load the `{$mode}` suggest mode.");
    }
}
