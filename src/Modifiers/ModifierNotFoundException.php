<?php

namespace Statamic\Modifiers;

use Exception;
use Facade\Ignition\Support\StringComparator;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Statamic;

class ModifierNotFoundException extends Exception implements ProvidesSolution
{
    protected $modifier;

    public function __construct($modifier)
    {
        parent::__construct("Modifier [{$modifier}] not found");

        $this->modifier = $modifier;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedModifier = $this->getSuggestedModifier())
            ? "Did you mean `$suggestedModifier`?"
            : 'Are you sure the modifier exists?';

        return BaseSolution::create("The {$this->modifier} modifier was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the modifiers guide' => Statamic::docsUrl('modifiers'),
            ]);
    }

    protected function getSuggestedModifier()
    {
        return StringComparator::findClosestMatch(
            app('statamic.modifiers')->keys()->all(),
            $this->modifier
        );
    }
}
