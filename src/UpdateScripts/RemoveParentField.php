<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Collection;

class RemoveParentField extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        Collection::all()->each(function ($collection) {
            $collection->entryBlueprints()->each(function ($blueprint) use ($collection) {
                if ($collection->hasStructure() && $blueprint->hasField('parent')) {
                    $blueprint->removeField('parent')->save();

                    $this->console->line(sprintf(
                        'Parent field removed from the <comment>%s</comment> collection\'s <info>%s</info> blueprint.',
                        $collection->handle(),
                        $blueprint->handle()
                    ));
                }
            });
        });
    }
}
