<?php

namespace Statamic\Widgets;

use Facades\Statamic\Marketplace\Marketplace;
use Facades\Statamic\Updater\UpdatesOverview;
use Statamic\Facades\Addon;
use Statamic\Facades\User;

class Updater extends Widget
{
    public function component()
    {
        if (! User::current()->can('view updates')) {
            return;
        }

        $items = collect(UpdatesOverview::updatableAddons())->map(function ($id) {
            $addon = Addon::get($id);

            $changelog = $addon->changelog();

            return [
                'name' => $addon->name(),
                'count' => $changelog->availableUpdatesCount(),
                'security' => $changelog->hasSecurityUpdate(),
                'url' => cp_route('updater.product', $addon->slug()),
            ];
        });

        if (UpdatesOverview::hasStatamicUpdate()) {
            $changelog = Marketplace::statamic()->changelog();
            $items->push([
                'name' => 'Statamic Core',
                'count' => $changelog->availableUpdatesCount(),
                'security' => $changelog->hasSecurityUpdate(),
                'url' => cp_route('updater.product', 'statamic'),
            ]);
        }

        return VueComponent::render('updater-widget', [
            'items' => $items,
        ]);
    }
}
