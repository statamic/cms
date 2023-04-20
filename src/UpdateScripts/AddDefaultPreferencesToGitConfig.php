<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\File;
use Statamic\Support\Str;

class AddDefaultPreferencesToGitConfig extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.3.62');
    }

    public function update()
    {
        if (! File::exists($path = config_path('statamic/git.php'))) {
            return;
        }

        $config = File::get($path);

        $addBelow = <<<'EOT'
        resource_path('forms'),
        resource_path('users'),
EOT;

        $replacement = <<<'EOT'
        resource_path('forms'),
        resource_path('users'),
        resource_path('preferences.yaml'),
EOT;

        if (Str::contains($config, $replacement) || ! Str::contains($config, $addBelow)) {
            return;
        }

        $config = str_replace($addBelow, $replacement, $config);

        File::put($path, $config);
    }
}
