<?php

namespace Statamic\View\Debugbar;

use DebugBar\DataCollector\ConfigCollector;

class VariableCollector extends ConfigCollector
{
    public function getWidgets(): array
    {
        $widgets = parent::getWidgets();

        $widgets['Variables']['icon'] = 'usd';

        return $widgets;
    }

    public function useHtmlVarDumper($value = true)
    {
        if (! config('statamic.system.debugbar.pretty_print_variables', true)) {
            $value = false;
        }

        if (method_exists(parent::class, 'useHtmlVarDumper')) {
            return parent::useHtmlVarDumper($value);
        }

        return $this;
    }
}
