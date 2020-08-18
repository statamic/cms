<?php

namespace Statamic\Ignition\Solutions;

use Facade\IgnitionContracts\RunnableSolution;
use Statamic\Facades\File;
use Statamic\Statamic;
use Statamic\Support\Str;

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
        $path = config_path('statamic/editions.php');

        $contents = File::get($path);

        if (! Str::contains($contents, "'pro' => false,")) {
            throw new \Exception('Could not reliably update the config file.');
        }

        $contents = str_replace("'pro' => false,", "'pro' => true,", $contents);

        File::put($path, $contents);
    }

    public function getRunParameters(): array
    {
        return [];
    }
}
