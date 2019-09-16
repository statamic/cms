<?php

namespace Statamic\CP;

class Breadcrumbs
{
    protected $crumbs;

    public function __construct($crumbs)
    {
        $this->crumbs = collect($crumbs);
    }

    public function toArray()
    {
        return $this->crumbs->toArray();
    }

    public function toJson()
    {
        return $this->crumbs->toJson();
    }

    public function title($title = null)
    {
        $crumbs = $this->crumbs->map->text;

        if ($title) {
            $crumbs->push(__($title));
        }

        return $crumbs->reverse()->join(' ‹ ');
    }
}
