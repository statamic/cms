<?php

namespace Statamic\Extend;

use Statamic\Data\ExistsAsFile;

class AddonSettings extends AbstractAddonSettings
{
    use ExistsAsFile;

    public function path()
    {
        return storage_path('statamic/addons/'.$this->addon()->id().'.yaml');
    }

    public function fileData()
    {
        return $this->values()->all();
    }
}
