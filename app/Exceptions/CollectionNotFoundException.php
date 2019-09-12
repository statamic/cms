<?php

namespace Statamic\Exceptions;

use Exception;
use Statamic\Facades\Collection;
use Facade\IgnitionContracts\Solution;
use Facade\IgnitionContracts\BaseSolution;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\ProvidesSolution;

class CollectionNotFoundException extends Exception implements ProvidesSolution
{
    protected $collection;

    public function __construct($collection)
    {
        parent::__construct("Collection [{$collection}] not found");

        $this->collection = $collection;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedCollection = $this->getSuggestedCollection())
            ? "Did you mean `$suggestedCollection`?"
            : 'Are you sure the collection exists?';

        return BaseSolution::create("The {$this->collection} collection was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the collections guide' => 'https://docs.statamic.com/collections',
            ]);
    }

    protected function getSuggestedCollection()
    {
        return StringComparator::findClosestMatch(
            Collection::handles()->all(),
            $this->collection
        );
    }
}
