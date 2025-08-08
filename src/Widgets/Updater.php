<?php

namespace Statamic\Widgets;

use Facades\Statamic\Marketplace\Marketplace;
use Facades\Statamic\Updater\UpdatesOverview;
use Statamic\Facades\Addon;
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

        $items = collect(UpdatesOverview::updatableAddons())->map(function ($id) {
            $addon = Addon::get($id);

            return [
                'name' => $addon->name(),
                'count' => $addon->changelog()->availableUpdatesCount(),
                'critical' => false,
                'url' => cp_route('updater.product', $addon->slug()),
            ];
        });

        if (UpdatesOverview::hasStatamicUpdate()) {
            $items->push([
                'name' => 'Statamic Core',
                'count' => Marketplace::statamic()->changelog()->availableUpdatesCount(),
                'critical' => false,
                'url' => cp_route('updater.product', 'statamic'),
            ]);
        }

        return view('statamic::widgets.updater', compact('items'));
    }
}
