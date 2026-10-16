<?php

namespace Statamic\Console\Composer;

use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Support\Arr;

class Json
{
    public static function filename(): string
    {
        // Read the env var using vanilla PHP so that this can be run in a Composer hook, where
        // Composer registers the class autoloader but never runs the `autoload.files` entries.
        // That means Laravel's helper functions don't exist, and `Env::get()` relies on `value()`.
        $filename = $_ENV['COMPOSER'] ?? $_SERVER['COMPOSER'] ?? getenv('COMPOSER');

        return trim((string) $filename) ?: 'composer.json';
    }

    public static function path(): string
    {
        $filename = static::filename();

        return Path::isAbsolute($filename) ? $filename : base_path($filename);
    }

    public static function isMissingPreUpdateCmd()
    {
        $composerJson = json_decode(File::get(static::path()), true);

        $scripts = Arr::get($composerJson, 'scripts.pre-update-cmd', []);

        return ! in_array(Scripts::class.'::preUpdateCmd', $scripts);
    }

    public static function addPreUpdateCmd()
    {
        if (! static::isMissingPreUpdateCmd()) {
            return false;
        }

        $composerJson = File::get($path = static::path());

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
