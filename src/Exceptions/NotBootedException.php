<?php

namespace Statamic\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Statamic\Statamic;

class NotBootedException extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('Statamic has not booted')
            ->setSolutionDescription('
                Code has been run that relies on Statamic having already been booted.\
                Typically found inside a service provider. Wrap your code in a `$this->app->booted` callback.'
            )->setDocumentationLinks([
                'Read the addons guide' => Statamic::docsUrl('extending/addons'),
            ]);
    }
}
