<?php

namespace Statamic\Data\Entries;

use Carbon\Carbon;
use Statamic\API\Config;
use Statamic\Data\Content\ContentFactory;
use Statamic\Contracts\Data\Entries\EntryFactory as EntryFactoryContract;

class EntryFactory extends ContentFactory implements EntryFactoryContract
{
    protected $slug;
    protected $collection;

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
     * @param string $collection
     * @return $this
     */
    public function collection($collection)
    {
        $this->collection = $collection;

        return $this;
    }

    /**
     * @param string $date
     * @return $this
     */
    public function date($date = null)
    {
        $hasTime = !is_null($date) && strlen($date) > 10;

        $date = ($date) ? Carbon::parse($date) : Carbon::now();

        $this->order = ($hasTime) ? $date->format('Y-m-d-Hi') : $date->format('Y-m-d');

        return $this;
    }

    /**
     * @return Entry
     */
    public function get()
    {
        $entry = new Entry;

        $entry->slug($this->slug);
        $entry->collection($this->collection);
        $entry->data($this->data);
        $entry->order($this->order);
        $entry->published($this->published);

        if ($this->path) {
            $entry->dataType(pathinfo($this->path)['extension']);
        } else {
            $entry->dataType(Config::get('system.default_extension'));
        }

        $entry = $this->identify($entry);

        $entry->syncOriginal();

        return $entry;
    }
}
