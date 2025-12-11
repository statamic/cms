<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Pagination\AbstractPaginator;
use Statamic\Fieldtypes\Entries as EntriesFieldtype;

class EntriesFieldtypeEntries extends Entries
{
    private EntriesFieldtype $fieldtype;
    public $collects = EntriesFieldtypeListedEntry::class;

    public function __construct($resource, EntriesFieldtype $fieldtype)
    {
        $this->fieldtype = $fieldtype;

        parent::__construct($resource);
    }

    protected function collectResource($resource)
    {
        $collection = parent::collectResource($resource);

        if ($collection instanceof AbstractPaginator) {
            $collection->getCollection()->each->fieldtype($this->fieldtype);
        } else {
            $collection->each->fieldtype($this->fieldtype);
        }

        return $collection;
    }
}
