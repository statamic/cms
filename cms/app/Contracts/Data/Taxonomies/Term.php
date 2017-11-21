<?php

namespace Statamic\Contracts\Data\Taxonomies;

use Statamic\Contracts\Data\Content\Content;
use Statamic\Data\Content\ContentCollection;

interface Term extends Content
{
    /**
     * The taxonomy to which this term belongs
     *
     * @return \Statamic\Contracts\Data\Taxonomies\Taxonomy
     */
    public function taxonomy();

    /**
     * The name of the taxonomy to which this term belongs
     *
     * @param string|null $taxonomy
     * @return string
     */
    public function taxonomyName($taxonomy = null);

    /**
     * Get or set the content that is related to this term
     *
     * @param ContentCollection|null $collection
     * @return ContentCollection
     */
    public function collection(ContentCollection $collection = null);

    /**
     * Get the number of content objects that related to this taxonomy
     *
     * @return int
     */
    public function count();
}
