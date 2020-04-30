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

    public function publish($options = [])
    {
        if (method_exists($this, 'revisionsEnabled') && $this->revisionsEnabled()) {
            return $this->publishWorkingCopy($options);
        }

        $this->published(true)->save();

        return $this;
    }

    public function unpublish($options = [])
    {
        if (method_exists($this, 'revisionsEnabled') && $this->revisionsEnabled()) {
            return $this->unpublishWorkingCopy($options);
        }

        $this->published(false)->save();

        return $this;
    }
}
