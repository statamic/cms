<?php

namespace Statamic\Data;

trait Publishable
{
    protected $published = true;

    public function published($published = null)
    {
        if (func_num_args() === 0) {
            return $this->published;
        }

        $this->published = $published;

        return $this;
    }

    public function publish()
    {
        $this->published = true;

        return $this;
    }

    public function unpublish()
    {
        $this->published = false;

        return $this;
    }
}
