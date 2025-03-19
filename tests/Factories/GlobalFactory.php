<?php

namespace Tests\Factories;

use Statamic\Facades\GlobalSet;

class GlobalFactory
{
    protected $id;
    protected $handle;
    protected $title;
    protected $data = [];
    protected $sites = ['en' => null];

    public function id($id)
    {
        $this->id = $id;

        return $this;
    }

    public function handle($handle)
    {
        $this->handle = $handle;

        return $this;
    }

    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    public function sites($sites)
    {
        $this->sites = $sites;

        return $this;
    }

    public function make()
    {
        $set = GlobalSet::make($this->handle)->sites($this->sites);

        $set->addLocalization(
            $set->makeLocalization($set->sites()->keys()->first())->data($this->data)
        );

        if ($this->id) {
            $set->id($this->id);
        }

        if ($this->title) {
            $set->title($this->title);
        }

        return $set;
    }

    public function create()
    {
        return tap($this->make())->save();
    }
}
