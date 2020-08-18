<?php

namespace Statamic\Widgets;

class GettingStarted extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        return view('statamic::widgets.getting-started');
    }
}
