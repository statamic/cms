<?php

namespace Statamic\Widgets;

class Template extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        $data = $this->config()->except('type', 'template')->all();

        return view($this->config('template'), $data);
    }
}
