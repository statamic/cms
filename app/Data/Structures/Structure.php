<?php

namespace Statamic\Data\Structures;

use Statamic\API\Structure as StructureAPI;
use Statamic\Contracts\Data\Structures\Structure as StructureContract;

class Structure implements StructureContract
{
    protected $handle;
    protected $data = [];

    public function handle($handle = null)
    {
        if (is_null($handle)) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function data($data = null)
    {
        if (is_null($data)) {
            return $this->data;
        }

        $this->data = $data;

        return $this;
    }

    public function title($title = null)
    {
        if (is_null($title)) {
            return array_get($this->data, 'title', ucfirst($this->handle()));
        }

        $this->data['title'] = $title;

        return $this;
    }

    public function save()
    {
        StructureAPI::save($this);
    }
}
