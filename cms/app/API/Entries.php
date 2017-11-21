<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class Entries
{
    /**
     * Get entries from a collection
     *
     * @param string      $collection
     * @param array|null  $slugs
     * @return \Statamic\Data\ContentCollection
     * @deprecated since 2.1
     */
    public static function getFromCollection($collection, $slugs = null)
    {
        \Log::notice('Entries::getFromCollection() is deprecated. Use Entry::whereCollection()');

        $entries = Entry::whereCollection($collection);

        if ($slugs) {
            $slugs = Helper::ensureArray($slugs);

            $entries = $entries->filter(function ($entry) use ($slugs) {
                return in_array($entry->slug(), $slugs);
            });
        }

        return $entries;
    }

    /**
     * @param $slug
     * @return \Statamic\Contracts\Data\Entries\Collection
     * @deprecated since 2.1
     */
    public static function createCollection($slug)
    {
        \Log::notice('Entries::createCollection() is deprecated. Use Collection::create()');

        return Collection::create($slug);
    }
}
