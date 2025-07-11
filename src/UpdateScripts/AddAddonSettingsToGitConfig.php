<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\File;
use Statamic\Support\Str;

class AddAddonSettingsToGitConfig extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        if (! File::exists($path = config_path('statamic/git.php'))) {
            return;
        }

        $config = File::get($path);

        $addBelow = <<<'EOT'
        base_path('users'),
        resource_path('blueprints'),
EOT;

        $replacement = <<<'EOT'
        base_path('users'),
        resource_path('addons'),
        resource_path('blueprints'),
EOT;

        if (Str::contains($config, $replacement) || ! Str::contains($config, $addBelow)) {
            return;
        }

        $config = str_replace($addBelow, $replacement, $config);

        File::put($path, $config);
    }
}
