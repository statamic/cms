<?php

namespace Statamic\Widgets;

use Statamic\Extend\Widget;
use Statamic\View\Antlers\View;

class GettingStarted extends Widget
{
    /**
     * The HTML that should be shown in the widget
     *
     * @return string
     */
    public function html()
    {
        return view('statamic::widgets.getting-started');
    }
}
