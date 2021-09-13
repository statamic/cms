<?php

namespace Statamic\Ignition\Solutions;

use Exception;
use Facade\IgnitionContracts\RunnableSolution;
use Facades\Statamic\UpdateScripts\Manager as UpdateScriptManager;
use Statamic\Console\Composer\Json as ComposerJson;
use Statamic\Console\NullConsole;
use Statamic\Statamic;

class EnableComposerUpdateScripts implements RunnableSolution
{
    public function getSolutionTitle(): string
    {
        return 'Your composer.json is not properly configured for Statamic update scripts';
    }

    public function getSolutionDescription(): string
    {
        return '';
    }

    public function getDocumentationLinks(): array
    {
        return [
            'Configuring update scripts on older installations' => Statamic::docsUrl('/knowledge-base/configuring-update-scripts'),
        ];
    }

    public function getSolutionActionDescription(): string
    {
        return 'Statamic can attempt to configure and run update scripts for you.';
    }

    public function getRunButtonText(): string
    {
        return 'Configure & Run Update Scripts';
    }

    public function run(array $parameters = [])
    {
        // Setup null console so we can detect console error output.
        $console = new NullConsole;

        // Attempt updates first.
        UpdateScriptManager::runUpdatesForSpecificPackageVersion(Statamic::PACKAGE, '3.0.0', $console);

        // If there was error output in console,
        // throw exception so that user can re-run ignition solution or click through docs link.
        if ($error = $console->getErrors()->first()) {
            throw new Exception($error);
        }

        // If update scripts were successful, configure user's composer.json.
        ComposerJson::addPreUpdateCmd();
    }

    public function getRunParameters(): array
    {
        return [];
    }
}
