<?php

namespace Statamic\Ignition\Solutions;

use Facade\IgnitionContracts\RunnableSolution;
use Statamic\Console\Composer\Json as ComposerJson;

class AddComposerPreUpdateCmd implements RunnableSolution
{
    public function getSolutionTitle(): string
    {
        return 'Composer script is not configured';
    }

    public function getSolutionDescription(): string
    {
        return 'Enable it by adding `Statamic\\\Console\\\Composer\\\Scripts::preUpdateCmd` to your composer.json\`s `scripts` section as a `pre-update-cmd`.';
    }

    public function getDocumentationLinks(): array
    {
        return [];
    }

    public function getSolutionActionDescription(): string
    {
        return 'Statamic can attempt to add it for you.';
    }

    public function getRunButtonText(): string
    {
        return 'Add Script';
    }

    public function run(array $parameters = [])
    {
        ComposerJson::addPreUpdateCmd();
    }

    public function getRunParameters(): array
    {
        return [];
    }
}
