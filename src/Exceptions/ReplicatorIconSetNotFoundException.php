<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\ErrorSolutions\Contracts\BaseSolution;
use Spatie\ErrorSolutions\Contracts\ProvidesSolution;
use Spatie\ErrorSolutions\Contracts\Solution;
use Spatie\ErrorSolutions\Support\Laravel\StringComparator;
use Statamic\Icons\IconManager;
use Statamic\Statamic;

class ReplicatorIconSetNotFoundException extends Exception implements ProvidesSolution
{
    protected $set;

    public function __construct($set)
    {
        parent::__construct("Icon set [{$set}] not defined");

        $this->set = $set;
    }

    public function getSolution(): Solution
    {
        $description = ($suggested = $this->getSuggestedSet())
            ? "Did you mean `$suggested`?"
            : 'Are you sure the icon set has been registered?';

        $description .= ' You can register icon sets by `Icons::register()` or by passing a `$directory` to `Sets::useIcons()`.';

        return BaseSolution::create("The {$this->set} icon set was not found.")
            ->setSolutionDescription($description)
            ->setDocumentationLinks([
                'Read about icons' => Statamic::docsUrl('/ui-components/icons'),
            ]);
    }

    protected function getSuggestedSet()
    {
        return StringComparator::findClosestMatch(
            array_keys(IconManager::all()),
            $this->set
        );
    }
}
