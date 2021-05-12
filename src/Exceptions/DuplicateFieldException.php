<?php

namespace Statamic\Exceptions;

use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Statamic;

class DuplicateFieldException extends \Exception implements ProvidesSolution
{
    private $handle;
    private $blueprint;

    public function __construct($handle, $blueprint)
    {
        $this->handle = $handle;
        $this->blueprint = $blueprint;

        parent::__construct("Duplicate field [{$handle}] on blueprint [{$blueprint->handle()}].");
    }

    public function getHandle()
    {
        return $this->handle;
    }

    public function getBlueprint()
    {
        return $this->blueprint;
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create('Two fields with the same handle are in the blueprint.')
            ->setSolutionDescription('Edit the blueprint to resolve.')
            ->setDocumentationLinks([
                'View blueprints' => cp_route('blueprints.index'),
                'Read the blueprints docs' => Statamic::docsUrl('blueprints'),
            ]);
    }
}
