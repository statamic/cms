<?php

namespace Statamic\Data\Globals;

use Statamic\Data\Content\ContentFactory;
use Statamic\Contracts\Data\Globals\GlobalFactory as GlobalFactoryContract;

class GlobalFactory extends ContentFactory implements GlobalFactoryContract
{
    protected $slug;

    /**
     * @param string $slug
     * @return $this
     */
    public function create($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return GlobalSet
     */
    public function get()
    {
        $global = new GlobalSet;

        $global->slug($this->slug);
        $global->data($this->data);

        if (! $this->path) {
            $this->path = $global->path();
        }
        
        $global = $this->identify($global);

        $global->syncOriginal();

        return $global;
    }
}
