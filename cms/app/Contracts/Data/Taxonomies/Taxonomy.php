<?php

namespace Statamic\Contracts\Data\Taxonomies;

use Statamic\Contracts\HasFieldset;
use Statamic\Contracts\Data\DataFolder;

interface Taxonomy extends DataFolder, HasFieldset
{
    /**
     * Get the terms
     *
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function terms();

    /**
     * Get the terms that have been associated with data
     *
     * @return \Statamic\Data\Taxonomies\TermCollection
     */
    public function associatedTerms();

    /**
     * Add a taxonomy term
     *
     * @param string $key
     * @param \Statamic\Contracts\Data\Taxonomies\Term $term
     */
    public function addTerm($key, $term);

    /**
     * Remove a term
     *
     * @param string $key
     */
    public function removeTerm($key);

    /**
     * Get the number of terms
     *
     * @return int
     */
    public function count();

    /**
     * Get or set the route definition
     *
     * @return string
     */
    public function route($route = null);
}
