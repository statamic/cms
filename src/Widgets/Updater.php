<?php

namespace Statamic\Widgets;

use Facades\Statamic\Updater\UpdatesOverview;
use Statamic\Facades\User;

class Updater extends Widget
{
    /**
     * The HTML that should be shown in the widget.
     *
     * @return \Illuminate\View\View
     */
    public function html()
    {
        if (! User::current()->can('view updates')) {
            return;
        }

        $count = UpdatesOverview::count();
        $hasStatamicUpdate = UpdatesOverview::hasStatamicUpdate();
        $updatableAddons = UpdatesOverview::updatableAddons();
        $limit = $this->config('limit', 5);

        return view('statamic::widgets.updater', compact('count', 'hasStatamicUpdate', 'updatableAddons', 'limit'));
    }
}
