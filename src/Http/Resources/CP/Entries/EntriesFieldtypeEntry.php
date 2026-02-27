<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\JsonResource;
use Statamic\Facades\User;
use Statamic\Fieldtypes\Entries as EntriesFieldtype;

class EntriesFieldtypeEntry extends JsonResource
{
    private EntriesFieldtype $fieldtype;

    public function __construct($resource, EntriesFieldtype $fieldtype)
    {
        $this->fieldtype = $fieldtype;

        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $data = [
            'id' => $this->resource->id(),
            'title' => $this->resource->value('title'),
            'status' => $this->resource->status(),
            'edit_url' => $this->resource->editUrl(),
            'editable' => User::current()->can('edit', $this->resource),
            'hint' => $this->fieldtype->getItemHint($this->resource),
        ];

        return ['data' => $data];
    }
}
