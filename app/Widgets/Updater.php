<?php

namespace Statamic\Widgets;

use Statamic\Extend\Widget;
use Facades\Statamic\Updater\UpdatesOverview;

class Updater extends Widget
{
    /**
     * The HTML that should be shown in the widget
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        $count = UpdatesOverview::count();
        $hasStatamicUpdate = UpdatesOverview::hasStatamicUpdate();
        $updatableAddons = UpdatesOverview::updatableAddons();

        return view('statamic::widgets.updater', compact('count', 'hasStatamicUpdate', 'updatableAddons'));
    }
}
