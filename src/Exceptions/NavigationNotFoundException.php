<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
use Spatie\ErrorSolutions\Support\Laravel\StringComparator;
use Statamic\Facades\Nav;
use Statamic\Statamic;

class NavigationNotFoundException extends Exception implements ProvidesSolution
{
    protected $navigation;

    public function __construct($navigation)
    {
        parent::__construct("Navigation [{$navigation}] not found");

        $this->navigation = $navigation;
    }

    public function getSolution(): Solution
    {
        $description = ($suggestedNavigation = $this->getSuggestedNavigation())
            ? "Did you mean `$suggestedNavigation`?"
            : 'Are you sure the navigation exists?';

        return BaseSolution::create("The {$this->navigation} navigation was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read the navigation guide' => Statamic::docsUrl('navigation'),
            ]);
    }

    protected function getSuggestedNavigation()
    {
        return StringComparator::findClosestMatch(
            Nav::all()->map->handle()->all(),
            $this->navigation
        );
    }
}
