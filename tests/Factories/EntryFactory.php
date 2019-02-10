<?php

namespace Tests\Factories;

use Statamic\API\Entry;
use Statamic\API\Collection;

class EntryFactory
{
    protected $id;
    protected $slug;
    protected $data = [];

    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    public function slug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function collection($collection)
    {
        $this->collection = $collection;
        return $this;
    }

    public function data($data)
    {
        $this->data = $data;
        return $this;
    }

    public function make()
    {
        $entry = Entry::make()->collection($this->createCollection());

        if ($this->id) {
            $entry->id($this->id);
        }

        $entry->in('en', function ($localized) {
            $localized
                ->slug($this->slug)
                ->data($this->data);
        });

        return $entry;
    }

    public function create()
    {
        return tap($this->make())->save();
    }

    protected function createCollection()
    {
        return Collection::make($this->collection)
            ->sites(['en'])
            ->save();
    }
}
