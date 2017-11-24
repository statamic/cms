<?php

namespace Statamic\API;

/**
 * @deprecated since 2.1
 */
class TaxonomyTerms
{
    /**
     * Get taxonomies from a group
     *
     * @param string      $taxonomy
     * @param array|null  $slugs
     * @return \Statamic\Data\Taxonomies\TermCollection
     * @deprecated since 2.1
     */
    public static function getFromTaxonomy($taxonomy, $slugs = null)
    {
        \Log::notice('TaxonomyTerms::getFromTaxonomy() is deprecated. Use Term::whereTaxonomy()');

        $terms = Term::whereTaxonomy($taxonomy);

        if ($slugs) {
            $slugs = Helper::ensureArray($slugs);

            $terms = $terms->filter(function ($taxonomy) use ($slugs) {
                return in_array($taxonomy->slug(), $slugs);
            });
        }

        return $terms;
    }
}
