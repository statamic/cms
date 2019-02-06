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
        if (! auth()->user()->can('view updates')) {
            return;
        }

        $count = UpdatesOverview::count();
        $hasStatamicUpdate = UpdatesOverview::hasStatamicUpdate();
        $updatableAddons = UpdatesOverview::updatableAddons();

        return view('statamic::widgets.updater', compact('count', 'hasStatamicUpdate', 'updatableAddons'));
    }
}
