<?php

namespace Statamic\CP;

use Illuminate\Contracts\Support\Arrayable;

class Breadcrumbs implements Arrayable
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
