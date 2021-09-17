<?php

namespace Statamic\Entries;

use Statamic\Events\EntryBlueprintFound;
use Statamic\Facades\Site;

class AddSiteColumnToBlueprint
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! Site::hasMultiple()) {
            return;
        }

        if (! in_array(request()->route()->getName(), $this->allowedRoutes())) {
            return;
        }

        $event->blueprint->ensureField('site', [
            'type' => 'sites',
            'max_items' => 1,
            'listable' => true,
        ]);
    }

    private function allowedRoutes()
    {
        return [
            'statamic.cp.collections.show',
            'statamic.cp.collections.entries.index',
            'statamic.cp.relationship.index',
        ];
    }
}
