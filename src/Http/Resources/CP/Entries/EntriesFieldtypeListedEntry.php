<?php

namespace Statamic\Http\Resources\CP\Entries;

use Statamic\Fieldtypes\Entries as EntriesFieldtype;

class EntriesFieldtypeListedEntry extends ListedEntry
{
    private EntriesFieldtype $fieldtype;

    public function fieldtype(EntriesFieldtype $fieldtype): self
    {
        $this->fieldtype = $fieldtype;

        return $this;
    }

    public function toArray($request)
    {
        $arr = parent::toArray($request);

        if (
            in_array($this->fieldtype->config('mode'), ['select', 'typeahead'])
            && ($hint = $this->fieldtype->getItemHint($this->resource))
        ) {
            $arr['hint'] = $hint;
        }

        return $arr;
    }
}
