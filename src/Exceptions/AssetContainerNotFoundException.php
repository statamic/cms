<?php

namespace Statamic\Exceptions;

use Exception;
use Statamic\Facades\AssetContainer;
use Facade\IgnitionContracts\Solution;
use Facade\IgnitionContracts\BaseSolution;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\ProvidesSolution;

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
                'Read the assets guide' => 'https://docs.statamic.com/assets',
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
