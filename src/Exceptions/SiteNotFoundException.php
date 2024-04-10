<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
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
        return BaseSolution::create("The [{$this->siteHandle}] site was not found.")
            ->setSolutionDescription('Check the spelling of the site handle in your [resources/sites.yaml].')
            ->setDocumentationLinks([
                'Read the multi-site guide' => Statamic::docsUrl('/multi-site#configuration'),
            ]);
    }
}
