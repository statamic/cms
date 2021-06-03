<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Collection;
use Statamic\Fields\Validator;

class AddUniqueSlugValidation extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.1.19');
    }

    public function update()
    {
        Collection::all()->each(function ($collection) {
            $collection->entryBlueprints()->each(function ($blueprint) use ($collection) {
                $this->updateBlueprint($collection, $blueprint);
            });
        });

        $this->console->warn("If you don't want unique slugs, remove the validation rule that was added to the slug fields.");
    }

    private function updateBlueprint($collection, $blueprint)
    {
        // First, save it. This is the most reliable way at the moment
        // to make sure the slug field exists in the blueprint properly.
        $blueprint->save();

        $rules = $blueprint->field('slug')->get('validate');

        // Make sure that it's an array, since it might
        // be configured as a pipe delimited string.
        $rules = Validator::explodeRules($rules);

        $rules[] = 'unique_entry_value:{collection},{id},{site}';

        $blueprint->ensureFieldHasConfig('slug', ['validate' => $rules]);

        $blueprint->save();

        $this->console->line(sprintf(
            'Unique slug validation added to the <comment>%s</comment> collection\'s <info>%s</info> blueprint.',
            $collection->handle(),
            $blueprint->handle()
        ));
    }
}
