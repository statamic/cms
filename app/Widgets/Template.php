<?php namespace Statamic\Widgets;

use Statamic\API\File;
use Statamic\API\Path;
use Statamic\API\Parse;
use Statamic\API\Config;
use Statamic\Extend\Widget;

class Template extends Widget
{
    /**
     * The HTML that should be shown in the widget
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        $data = $this->config()->except('type', 'template')->all();

        return view($this->config('template'), $data);
    }
}
