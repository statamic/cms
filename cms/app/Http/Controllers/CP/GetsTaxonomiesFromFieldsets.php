<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Taxonomy;

trait GetsTaxonomiesFromFieldsets
{
    /**
     * Get the taxonomies that should be used.
     *
     * @param FieldsetContract $fieldset  The fieldset being used on the the content.
     * @return array                      An array of taxonomy configurations suitable for taxonomy fieldtypes.
     */
    protected function getTaxonomies($fieldset)
    {
        // Get the taxonomy configuration from the fieldset.
        $taxonomies = array_get($fieldset->contents(), 'taxonomies');

        // If the user has explicitly asked for no taxonomies, that's what they'll get.
        if ($taxonomies === false) {
            return [];
        }

        // Get all the taxonomies in the system and convert them to fieldtype configurations.
        $configurations = Taxonomy::all()->map(function ($taxonomy) {
            return [
                'type' => 'taxonomy',
                'taxonomy' => $taxonomy->path(),
                'title' => $taxonomy->title(),
                'create' => true
            ];
        })->sortBy('title')->values();

        // If there are no taxonomies configured, we'll just display them all.
        if (! $taxonomies) {
            return $configurations->all();
        }

        // If a plain ol' list of taxonomy handles was provided, reformat it to empty arrays.
        if (array_keys($taxonomies)[0] === 0) {
            $ts = [];
            foreach ($taxonomies as $t) {
                $ts[$t] = [];
            }
            $taxonomies = $ts;
        }

        // If taxonomies are configured, we only want to display those, and we'll
        // merge the config for each one into the existing collection.
        $configurations = $configurations->filter(function ($config) use ($taxonomies) {
            return in_array($config['taxonomy'], array_keys($taxonomies));
        })->map(function ($config) use ($taxonomies) {
            $handle = $config['taxonomy'];

            // Get the custom config. It may be an array, or just "true" if no
            // configuration was required, but still wanted it to be visible.
            $custom = $taxonomies[$handle];
            $custom = is_array($custom) ? $custom : [];

            return array_merge($config, $custom);
        });

        // Sort the fields by the order in which they were provided.
        if ($taxonomies) {
            $configurations = $configurations->sortBy(function ($arr) use ($taxonomies) {
                return array_search($arr['taxonomy'], array_keys($taxonomies));
            });
        }

        return $configurations->values()->all();
    }
}
