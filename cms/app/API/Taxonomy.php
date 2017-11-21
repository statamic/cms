<?php

namespace Statamic\API;

use Statamic\Data\Services\TaxonomiesService;

class Taxonomy
{
    /**
     * Get all collections
     *
     * @return \Illuminate\Support\Collection
     */
    public static function all()
    {
        return app(TaxonomiesService::class)->all()->sortBy(function ($taxonomy) {
            return $taxonomy->title();
        });
    }

    /**
     * Get the handles of all collections
     *
     * @return array
     */
    public static function handles()
    {
        return self::all()->keys()->all();
    }

    /**
     * Get a collection by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public static function whereHandle($handle)
    {
        return app(TaxonomiesService::class)->handle($handle);
    }

    /**
     * Check if a collection exists by its handle
     *
     * @param string $handle
     * @return bool
     */
    public static function handleExists($handle)
    {
        return self::whereHandle($handle) !== null;
    }

    /**
     * Create a taxonomy
     *
     * @param $slug
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public static function create($slug)
    {
        /** @var \Statamic\Contracts\Data\Taxonomies\Taxonomy $taxonomy */
        $taxonomy = app('Statamic\Contracts\Data\Taxonomies\Taxonomy');

        $taxonomy->path($slug);

        return $taxonomy;
    }
}