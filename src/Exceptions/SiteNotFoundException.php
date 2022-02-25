<?php

namespace Statamic\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;
use Statamic\Statamic;

class SiteNotFoundException extends Exception implements ProvidesSolution
{
    protected $siteHandle;

    public function __construct($siteHandle)
    {
        parent::__construct("Site [{$siteHandle}] not found");

        $this->siteHandle = $siteHandle;
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create("The {$this->siteHandle} site was not found.")
            ->setSolutionDescription('Check the spelling of the site handle in your sites.php config.')
            ->setDocumentationLinks([
                'Read the multi-site guide' => Statamic::docsUrl('/multi-site#configuration'),
            ]);
    }
}
