<?php

namespace Statamic\View\Debugbar;

use DebugBar\DataCollector\ConfigCollector;

class VariableCollector extends ConfigCollector
{
    public function getWidgets()
    {
        $widgets = parent::getWidgets();

        $widgets['Variables']['icon'] = 'usd';

        return $widgets;
    }

    public function useHtmlVarDumper($value = true)
    {
        if (! config('statamic.antlers.debugbar.prettyPrintVariables', true)) {
            $this->useHtmlVarDumper = false;

            return $this;
        }

        return parent::useHtmlVarDumper($value);
    }
}
