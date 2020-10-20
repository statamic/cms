<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Facades\Taxonomy;
use Statamic\Statamic;

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
                'Read the taxonomies guide' => Statamic::docsUrl('/taxonomies'),
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
