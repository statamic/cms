<?php

namespace Statamic\Extend\Management;

use Statamic\Exceptions\ResourceNotFoundException;

class WidgetLoader
{
    public function load($name, $config)
    {
        if (! ($widgets = app('statamic.widgets'))->has($name)) {
            throw new ResourceNotFoundException("Could not find files to load the `{$name}` widget.");
        }

        return $this->init($widgets->get($name), $config);
    }

    private function init($class, $config)
    {
        return tap(app($class), function ($widget) use ($config) {
            $widget->setConfig($config);
        });
    }
}
