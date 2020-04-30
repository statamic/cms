<?php

namespace Statamic\Widgets;

class Header extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        $classes = $this->config('classes', 'w-full');
        $text = $this->config('text');

        return view('statamic::widgets.header', compact('classes', 'text'));
    }
}
