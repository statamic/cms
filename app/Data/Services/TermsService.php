<?php

namespace Statamic\Data\Services;

use Statamic\API\Str;
use Statamic\Contracts\Data\Taxonomies\Term;
use Statamic\API\Data;
use Statamic\Data\Taxonomies\TermCollection;
use Statamic\Stache\Stache;

class TermsService
{
    /**
     * @var \Statamic\Stache\Stache
     */
    protected $stache;

    protected $taxonomyStache;

    /**
     * @param \Statamic\Stache\Stache $stache
     */
    public function __construct(Stache $stache)
    {
        $this->stache = $stache;
        $this->taxonomyStache = $stache->taxonomies;
    }

    /**
     * Get all the terms
     *
     * @param bool $includeUnassociated  Whether to include unassociated terms.
     * @return TermCollection
     */
    public function all($includeUnassociated = true)
    {
        return $this->sort(
            $this->taxonomyStache->getTerms($includeUnassociated)
        );
    }

    /**
     * Get all the terms in a taxonomy
     *
     * @param string $taxonomy             The taxonomy handle
     * @param bool   $includeUnassociated  Whether to include unassociated terms.
     * @return TermCollection
     */
    public function taxonomy($taxonomy, $includeUnassociated = true)
    {
        $collection = ($includeUnassociated)
            ? $this->taxonomyStache->getTermsInTaxonomy($taxonomy)
            : $this->taxonomyStache->getAssociatedTermsInTaxonomy($taxonomy);

        return $this->sort($collection);
    }

    private function sort($collection)
    {
        $collection = $collection->all();

        ksort($collection);

        return collect_terms($collection);
    }

    public function id($id)
    {
        if (! Str::contains($id, '/')) {
            return;
        }

        list($taxonomy, $slug) = explode('/', $id);

        return $this->taxonomyStache->getTermBySlug($slug, $taxonomy);
    }

    /**
     * Get a term by slug
     *
     * @param string      $slug
     * @param string|null $taxonomy  Optionally restrict to a taxonomy
     * @return Term
     * @todo get a term by slug without specifying the taxonomy.
     */
    public function slug($slug, $taxonomy = null)
    {
        return $this->taxonomyStache->getTermBySlug($slug, $taxonomy);
    }

    /**
     * Get a term by URI
     *
     * @param string $uri
     * @return Term
     */
    public function uri($uri)
    {
        $stache = $this->taxonomyStache;

        if (! $key = $stache->uris(default_locale())->filter()->flip()->get($uri)) {
            return null;
        }

        list($taxonomy, $slug) = explode('/', $key);

        return $this->slug($slug, $taxonomy);
    }

    public function collection(Term $term)
    {
        return collect_content($this->taxonomyStache->getAssociations($term)->unique()->map(function ($id) {
            return Data::find($id);
        }));
    }

    public function getDefaultTermUri($locale, $uri)
    {
        $stache = $this->taxonomyStache;

        // Attempt to get the term key of a localized URI. If a key is found, it means
        // a localized version exists. We'll then need to get the default version.
        if ($key = $stache->uris($locale)->filter()->flip()->get($uri)) {
            return $stache->uris(default_locale())->get($key);
        }

        // If there's no localized version, we'll check if there's a default one. If there is,
        // great, we must have been supplied with one. If not, the supplied URI is invalid.
        return $stache->uris(default_locale())->filter()->flip()->has($uri) ? $uri : null;
    }

    public function exists($id, $includeUnassociated = true)
    {
        list($taxonomy, $slug) = explode('/', $id);

        return $this->taxonomyStache->slugExists($slug, $taxonomy, $includeUnassociated);
    }

    public function slugExists($slug, $taxonomy, $includeUnassociated = true)
    {
        return $this->taxonomyStache->slugExists($slug, $taxonomy, $includeUnassociated);
    }
}
