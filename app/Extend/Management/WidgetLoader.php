<?php

namespace Statamic\Extend\Management;

use Statamic\API\Str;
use Statamic\Exceptions\ResourceNotFoundException;

class WidgetLoader
{
    public function load($widget, $config)
    {
        // An addon may contain multiple widgets. You may specify a "secondary" widget by delimiting with a dot.
        // For example, "{{ bacon.bits }}" would reference the "BitsWidget" in the "Bacon" addon.
        if (Str::contains($widget, '.')) {
            list($addon, $name) = explode('.', $widget);
        } else {
            $addon = $name = $widget;
        }

        $name = Str::studly($name);
        $addon = Str::studly($addon);
        $root = "Statamic\\Addons\\$addon";

        // Widgets may be stored in the root of the addon directory, named using YourAddonWidget.php or
        // secondary ones may be named SecondaryModifier.php. Classes in the root will take precedence.
        if (class_exists($rootClass = "{$root}\\{$name}Widget")) {
            return $this->init($rootClass, $config);
        }

        // Alternatively, widgets may be placed in a "Widgets" namespace.
        if (class_exists($namespacedClass = "{$root}\\Widgets\\{$name}Widget")) {
            return $this->init($namespacedClass, $config);
        }

        throw new ResourceNotFoundException("Could not find files to load the `{$widget}` widget.");
    }

    private function init($class, $config)
    {
        return tap(app($class), function ($widget) use ($config) {
            $widget->setParameters($config);
        });
    }
}
