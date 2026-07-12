<?php

namespace Statamic\Entries;

use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\Site;

class AddSiteColumnToBlueprint
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! Site::hasMultiple() || ! $this->isOnAllowedRoute()) {
            return;
        }

        $event->blueprint->ensureField('site', [
            'type' => 'sites',
            'max_items' => 1,
            'listable' => true,
        ]);
    }

    private function isOnAllowedRoute()
    {
        if (! $route = optional(request()->route())->getName()) {
            return false;
        }

        return in_array($route, [
            'statamic.cp.collections.show',
            'statamic.cp.collections.entries.index',
            'statamic.cp.relationship.index',
        ]);
    }
}
