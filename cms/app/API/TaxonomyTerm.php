<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class TaxonomyTerm
{
    /**
     * @param string $slug
     * @return \Statamic\Contracts\Data\Taxonomies\TermFactory
     * @deprecated since 2.1
     */
    public static function create($slug)
    {
        \Log::notice('TaxonomyTerm::create() is deprecated. Use Term::create()');

        return Term::create($slug);
    }

    /**
     * Get a term from a taxonomy, by its slug
     *
     * @param string      $taxonomy
     * @param string      $slug
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     * @deprecated since 2.1
     */
    public static function getFromTaxonomy($taxonomy, $slug)
    {
        \Log::notice('TaxonomyTerm::getFromTaxonomy() is deprecated. Use Term::whereSlug()');

        return Term::whereSlug($slug, $taxonomy);
    }

    /**
     * Get a taxonomy by UUID
     *
     * @param string $uuid
     * @return \Statamic\Contracts\Data\Taxonomies\Term
     * @deprecated since 2.1
     */
    public static function getByUuid($uuid)
    {
        \Log::notice('TaxonomyTerm::getByUuid() is deprecated. Use Term::find()');

        return Term::find($uuid);
    }
}
