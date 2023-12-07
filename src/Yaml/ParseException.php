<?php

namespace Statamic\Yaml;

use ErrorException;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Statamic\Statamic;

class ParseException extends ErrorException implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        [$title, $desc] = $this->getSolutionParts();

        return BaseSolution::create($title)
            ->setSolutionDescription($desc)
            ->setDocumentationLinks([
                'YAML Primer' => Statamic::docsUrl('/yaml'),
            ]);
    }

    protected function getSolutionParts()
    {
        if ($this->message === 'You cannot have a YAML variable named "content" while document content is present') {
            $description = 'If `content` is a string, it can go under the `---`. Otherwise, it should go in the front-matter.';
        }

        return [
            'Invalid YAML',
            $description ?? 'Correct any syntax errors. You may have used YAML 1.0 syntax, but 1.2 is expected.',
        ];
    }
}
