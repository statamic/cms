<?php

namespace Statamic\Exceptions;

use Exception;
use Statamic\Facades\Taxonomy;
use Facade\IgnitionContracts\Solution;
use Facade\IgnitionContracts\BaseSolution;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\ProvidesSolution;

class TaxonomyNotFoundException extends Exception implements ProvidesSolution
{
    protected $taxonomy;

    public function __construct($taxonomy)
    {
        parent::__construct("Taxonomy [{$taxonomy}] not found");

        $this->taxonomy = $taxonomy;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedTaxonomy = $this->getSuggestedTaxonomy())
            ? "Did you mean `$suggestedTaxonomy`?"
            : 'Are you sure the taxonomy exists?';

        return BaseSolution::create("The {$this->taxonomy} taxonomy was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the taxonomies guide' => 'https://docs.statamic.com/taxonomies',
            ]);
    }

    protected function getSuggestedTaxonomy()
    {
        return StringComparator::findClosestMatch(
            Taxonomy::handles()->all(),
            $this->taxonomy
        );
    }
}
