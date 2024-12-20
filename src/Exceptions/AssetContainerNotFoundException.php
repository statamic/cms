<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
use Spatie\ErrorSolutions\Support\Laravel\StringComparator;
use Statamic\Facades\AssetContainer;
use Statamic\Statamic;

class AssetContainerNotFoundException extends Exception implements ProvidesSolution
{
    protected $container;

    public function __construct($container)
    {
        parent::__construct("Asset Container [{$container}] not found");

        $this->container = $container;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedContainer = $this->getSuggestedContainer())
            ? "Did you mean `$suggestedContainer`?"
            : 'Are you sure the asset container exists?';

        return BaseSolution::create("The {$this->container} asset container was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the assets guide' => Statamic::docsUrl('/assets'),
            ]);
    }

    protected function getSuggestedContainer()
    {
        return StringComparator::findClosestMatch(
            AssetContainer::all()->map->handle()->all(),
            $this->container
        );
    }
}
