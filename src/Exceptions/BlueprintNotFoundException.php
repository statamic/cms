<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Spatie\LaravelIgnition\Support\StringComparator;
use Statamic\Facades\Blueprint;
use Statamic\Statamic;

class BlueprintNotFoundException extends Exception implements ProvidesSolution
{
    protected $blueprintHandle;
    protected $namespace;

    public function __construct($blueprintHandle, $namespace = null)
    {
        parent::__construct("Blueprint [{$blueprintHandle}] not found");

        $this->blueprintHandle = $blueprintHandle;
        $this->namespace = $namespace;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedBlueprint = $this->getSuggestedBlueprint())
            ? "Did you mean `$suggestedBlueprint`?"
            : 'Are you sure the blueprint exists?';

        return BaseSolution::create("The {$this->blueprintHandle} blueprint was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the blueprints guide' => Statamic::docsUrl('/blueprints'),
            ]);
    }

    protected function getSuggestedBlueprint()
    {
        if (! $this->namespace) {
            return null;
        }

        return StringComparator::findClosestMatch(
            Blueprint::in($this->namespace)->map->handle()->values()->all(),
            $this->blueprintHandle
        );
    }
}
