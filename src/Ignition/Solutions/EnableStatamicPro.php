<?php

namespace Statamic\Ignition\Solutions;

use Facade\IgnitionContracts\RunnableSolution;
use Statamic\Statamic;

class EnableStatamicPro implements RunnableSolution
{
    public function getSolutionTitle(): string
    {
        return 'Statamic Pro is required';
    }

    public function getSolutionDescription(): string
    {
        return 'Enable it by setting `\'pro\' => true` in `config/statamic/editions.php`.';
    }

    public function getDocumentationLinks(): array
    {
        return [
            'Licensing documentation' => Statamic::docsUrl('licensing'),
        ];
    }

    public function getSolutionActionDescription(): string
    {
        return 'Statamic can attempt to enable it for you.';
    }

    public function getRunButtonText(): string
    {
        return 'Enable Statamic Pro';
    }

    public function run(array $parameters = [])
    {
        Statamic::enablePro();
    }

    public function getRunParameters(): array
    {
        return [];
    }
}
