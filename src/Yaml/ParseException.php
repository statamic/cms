<?php

namespace Statamic\Yaml;

use ErrorException;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

class ParseException extends ErrorException implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('Invalid YAML encountered')
            ->setSolutionDescription('Correct any syntax errors. You may have used YAML 1.0 syntax, but 1.2 is expected.')
            ->setDocumentationLinks([
                'YAML Primer' => 'https://docs.statamic.com/yaml',
            ]);
    }
}
