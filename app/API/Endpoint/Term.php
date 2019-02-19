<?php

namespace Statamic\API\Endpoint;

use Statamic\Data\Services\TermsService;

class Term
{
    /**
     * The service for interacting with term
     *
     * @return TermsService
     */
    private function service()
    {
        return app(TermsService::class);
    }

    /**
     * Find an term by ID
     *
     * A term ID is the taxonomy handle and the slug joined by a slash.
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function find($id)
    {
        list($taxonomy, $slug) = explode('/', $id);

        return self::service()->slug($slug, $taxonomy);
    }

    /**
     * Get all terms
     *
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function all()
    {
        return self::service()->all();
    }

    /**
     * Get terms in a taxonomy
     *
     * @param string $taxonomy             The taxonomy handle.
     * @param bool   $includeUnassociated  Whether to include terms that have not been associated with data.
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function whereTaxonomy($taxonomy, $includeUnassociated = true)
    {
        return self::service()->taxonomy($taxonomy, $includeUnassociated);
    }

    /**
     * Get a term by slug and taxonomy
     *
     * @param string $slug
     * @param string $taxonomy
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function whereSlug($slug, $taxonomy)
    {
        return self::service()->slug($slug, $taxonomy);
    }

    /**
     * Get a term by URI
     *
     * @param string $uri
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     */
    public function whereUri($uri)
    {
        return self::service()->uri($uri);
    }

    /**
     * Check if a term exists
     *
     * @param string $id
     * @param bool   $includeUnassociated  Whether to include unassociated terms.
     * @return bool
     */
    public function exists($id, $includeUnassociated = true)
    {
        return self::service()->exists($id, $includeUnassociated);
    }

    /**
     * Check if a term exists by slug
     *
     * @param string $slug
     * @param string $taxonomy
     * @param bool   $includeUnassociated  Whether to include unassociated terms.
     * @return bool
     */
    public function slugExists($slug, $taxonomy, $includeUnassociated = true)
    {
        return self::service()->slugExists($slug, $taxonomy, $includeUnassociated);
    }

    /**
     * Get a normalized slug
     *
     * A term's slug may potentially contain spaces, localized characters,
     * and more. Slugifying the string will normalize these cases
     * and give us a string that's usable within URLs.
     *
     * @param string $slug
     * @return string
     */
    public function normalizeSlug($slug)
    {
        return slugify($slug);
    }
}
