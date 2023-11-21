<?php

namespace Tests\Factories;

use Statamic\Contracts\Entries\Collection as StatamicCollection;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class EntryFactory
{
    protected $id;
    protected $slug;
    protected $data;
    protected $date;
    protected $published;
    protected $order;
    protected $locale;
    protected $origin;
    protected $collection;
    protected $blueprint;

    public function __construct()
    {
        $this->reset();
    }

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

    public function date($date)
    {
        $this->date = $date;

        return $this;
    }

    public function published($published)
    {
        $this->published = $published;

        return $this;
    }

    public function locale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    public function origin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function make()
    {
        $entry = Entry::make()
            ->locale($this->locale)
            ->collection($collection = $this->createCollection())
            ->slug($this->slug)
            ->data($this->data)
            ->origin($this->origin)
            ->published($this->published)
            ->blueprint($this->blueprint);

        if ($collection->dated()) {
            $entry->date($this->date);
        }

        if ($this->id) {
            $entry->id($this->id);
        }

        $this->reset();

        return $entry;
    }

    public function create()
    {
        return tap($this->make())->save();
    }

    protected function createCollection()
    {
        if ($this->collection instanceof StatamicCollection) {
            return $this->collection;
        }

        return Collection::findByHandle($this->collection)
            ?? Collection::make($this->collection)
                ->sites(['en'])
                ->save();
    }

    private function reset()
    {
        $this->id = null;
        $this->slug = null;
        $this->data = [];
        $this->date = null;
        $this->published = true;
        $this->order = null;
        $this->locale = 'en';
        $this->origin = null;
        $this->collection = null;
        $this->blueprint = null;
    }
}
