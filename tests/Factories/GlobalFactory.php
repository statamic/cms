<?php

namespace Tests\Factories;

use Statamic\Facades\GlobalSet;

class GlobalFactory
{
    protected $id;
    protected $handle;
    protected $data = [];
    protected $site = 'en';

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

    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    public function site($handle)
    {
        $this->site = $handle;

        return $this;
    }

    public function make()
    {
        $set = GlobalSet::make($this->handle);

        $set->addLocalization(
            $set->makeLocalization($this->site)->data($this->data)
        );

        if ($this->id) {
            $set->id($this->id);
        }

        return $set;
    }

    public function create()
    {
        return tap($this->make())->save();
    }
}
