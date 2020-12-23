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
}
