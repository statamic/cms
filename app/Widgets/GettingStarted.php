<?php

namespace Statamic\Widgets;

use Statamic\Extend\Widget;

class GettingStarted extends Widget
{
    /**
     * The HTML that should be shown in the widget
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        return view('statamic::widgets.getting-started');
    }
}
