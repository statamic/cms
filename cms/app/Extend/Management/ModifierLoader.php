<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\Exceptions\ResourceNotFoundException;

class ModifierLoader
{
    public function load($modifier)
    {
        // An addon may contain multiple modifiers. You may specify a "secondary" modifier by delimiting with a dot.
        // For example, "{{ var | bacon.bits }}" would reference the "BitsModifier" in the "Bacon" addon.
        if (Str::contains($modifier, '.')) {
            list($addon, $name) = explode('.', $modifier);
        } else {
            $addon = $name = $modifier;
        }

        $name = Str::studly($name);
        $addon = Str::studly($addon);
        $root = "Statamic\\Addons\\$addon";

        // Modifiers may be stored in the root of the addon directory, named using YourAddonModifier.php or
        // secondary ones may be named SecondaryModifier.php. Classes in the root will take precedence.
        if (class_exists($rootClass = "{$root}\\{$name}Modifier")) {
            return app($rootClass);
        }

        // Alternatively, modifiers may be placed in a "Modifiers" namespace.
        if (class_exists($namespacedClass = "{$root}\\Modifiers\\{$name}Modifier")) {
            return app($namespacedClass);
        }

        throw new ResourceNotFoundException("Could not find files to load the `{$modifier}` modifier.");
    }
}
