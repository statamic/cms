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
}
