<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Facades\Blueprint;
use Statamic\Statamic;

class BlueprintDoesNotExistException extends Exception implements ProvidesSolution
{
    protected $blueprintHandle;

    public function __construct($blueprintHandle)
    {
        parent::__construct("Blueprint [{$blueprintHandle}] not found");

        $this->blueprintHandle = $blueprintHandle;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedCollection = $this->getSuggestedCollection())
            ? "Did you mean `$suggestedCollection`?"
            : 'Are you sure the blueprint exists?';

        return BaseSolution::create("The {$this->blueprintHandle} blueprint was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the blueprints guide' => Statamic::docsUrl('/blueprints'),
            ]);
    }

    protected function getSuggestedCollection()
    {
        return StringComparator::findClosestMatch(
            Blueprint::in('.')->map->handle()->flatten()->all(),
            $this->blueprintHandle
        );
    }
}
