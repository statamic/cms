<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\Exceptions\ResourceNotFoundException;

class FieldtypeLoader
{
    public function load($fieldtype, $config)
    {
        $fieldtype = $this->getAlias($fieldtype);

        // An addon may contain multiple fieldtypes. You may specify a "secondary" fieldtype by delimiting
        // with a dot. For example, "bacon.bits" would reference the "BitsFieldtype" in the "Bacon" addon.
        if (Str::contains($fieldtype, '.')) {
            list($addon, $name) = explode('.', $fieldtype);
        } else {
            $addon = $name = $fieldtype;
        }

        $name = Str::studly($name);
        $addon = Str::studly($addon);
        $root = "Statamic\\Addons\\$addon";

        // Fieldtypes may be stored in the root of the addon directory, named using YourAddonFieldtype.php or
        // secondary ones may be named SecondaryFieldtype.php. Classes in the root will take precedence.
        if (class_exists($rootClass = "{$root}\\{$name}Fieldtype")) {
            return $this->init($rootClass, $config);
        }

        // Alternatively, fieldtypes may be placed in a "Fieldtypes" namespace.
        if (class_exists($namespacedClass = "{$root}\\Fieldtypes\\{$name}Fieldtype")) {
            return $this->init($namespacedClass, $config);
        }

        throw new ResourceNotFoundException("Could not find files to load the `{$fieldtype}` fieldtype.");
    }

    private function init($class, $config)
    {
        return tap(app($class), function ($fieldtype) use ($config) {
            $fieldtype->setFieldConfig($config);
        });
    }

    /**
     * Parse for fieldtype aliases
     *
     * @param string $original Original fieldtype to check for
     * @return string
     */
    private function getAlias($original)
    {
        switch ($original) {
            case "list":
                return "lists";

            case 'array':
                return 'arr';

            default:
                return $original;
        }
    }
}
