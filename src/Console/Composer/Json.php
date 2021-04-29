<?php

namespace Statamic\Console\Composer;

use Statamic\Facades\File;
use Statamic\Support\Arr;

class Json
{
    public static function isMissingPreUpdateCmd()
    {
        $composerJson = json_decode(File::get(base_path('composer.json')), true);

        $scripts = Arr::get($composerJson, 'scripts.pre-update-cmd', []);

        return ! in_array(Scripts::class.'::preUpdateCmd', $scripts);
    }

    public static function addPreUpdateCmd()
    {
        if (! static::isMissingPreUpdateCmd()) {
            return false;
        }

        $composerJson = File::get($path = base_path('composer.json'));

        $preUpdateCmdScript = str_replace('\\', '\\\\\\', Scripts::class.'::preUpdateCmd');

        $preUpdateCmdsArray = <<<"EOT"
        "pre-update-cmd": [
            "$preUpdateCmdScript"
        ],\n
EOT;

        $preUpdateCmdsExist = Arr::get(json_decode($composerJson, true), 'scripts.pre-update-cmd', false);

        if ($preUpdateCmdsExist) {
            $composerJson = preg_replace('/("pre-update-cmd".*\n)/m', "$1            \"$preUpdateCmdScript\",\n", $composerJson);
        } else {
            $composerJson = preg_replace('/("scripts".*\n)/m', '$1'.$preUpdateCmdsArray, $composerJson);
        }

        $success = Arr::get(json_decode($composerJson, true), 'scripts.pre-update-cmd', false);

        if ($success === false) {
            throw new \Exception('Statamic had trouble adding the `pre-update-cmd` to your composer.json file.');
        }

        File::put($path, $composerJson);
    }
}
