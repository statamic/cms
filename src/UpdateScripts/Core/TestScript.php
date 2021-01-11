<?php

namespace Statamic\UpdateScripts\Core;

use Statamic\UpdateScripts\UpdateScript;

class TestScript extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.1.0');
    }

    public function update()
    {
        cache()->put('seo-pro-update-successful', true);
    }
}
