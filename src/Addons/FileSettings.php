<?php

namespace Statamic\Addons;

use Statamic\Addons\Settings as AbstractSettings;
use Statamic\Data\ExistsAsFile;

class FileSettings extends AbstractSettings
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
