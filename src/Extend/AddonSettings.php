<?php

namespace Statamic\Extend;

use Statamic\Data\ExistsAsFile;

class AddonSettings extends AbstractAddonSettings
{
    use ExistsAsFile;

    public function path()
    {
        return resource_path("addons/{$this->addon()->slug()}.yaml");
    }

    public function fileData()
    {
        return $this->rawValues();
    }
}
