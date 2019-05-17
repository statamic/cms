<?php

namespace Statamic\API\Endpoint;

use Statamic\Data\Services\TaxonomiesService;
use Statamic\Contracts\Data\Repositories\TaxonomyRepository;
use Statamic\Contracts\Data\Taxonomies\Taxonomy as TaxonomyContract;

class Taxonomy
{
    /**
     * Get all collections
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->repo()->all()->sortBy(function ($taxonomy) {
            return $taxonomy->title();
        });
    }

    /**
     * Get the handles of all collections
     *
     * @return array
     */
    public function handles()
    {
        return self::all()->keys()->all();
    }

    /**
     * Get a collection by handle
     *
     * @param string $handle
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function findByHandle($handle)
    {
        return app(TaxonomiesService::class)->handle($handle);
    }

    /**
     * Check if a collection exists by its handle
     *
     * @param string $handle
     * @return bool
     */
    public function handleExists($handle)
    {
        return self::findByHandle($handle) !== null;
    }

    public function save(TaxonomyContract $taxonomy)
    {
        $this->repo()->save($taxonomy);
    }

    /**
     * Create a taxonomy
     *
     * @param $slug
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function create($slug)
    {
        /** @var \Statamic\Contracts\Data\Taxonomies\Taxonomy $taxonomy */
        $taxonomy = app('Statamic\Contracts\Data\Taxonomies\Taxonomy');

        $taxonomy->path($slug);

        return $taxonomy;
    }

    protected function repo(): TaxonomyRepository
    {
        return app(TaxonomyRepository::class);
    }
}
