<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use ArrayObject;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

class Terms extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ListedTerm::class;
    protected $blueprint;
    protected $columnPreferenceKey;
    protected $columns;

    public function blueprint($blueprint)
    {
        $this->blueprint = $blueprint;

        return $this;
    }

    public function columnPreferenceKey($key)
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    protected function setColumns()
    {
        $columns = $this->blueprint->columns();

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();
    }

    public function toArray($request)
    {
        $this->setColumns();

        $taxonomies = new ArrayObject;

        return $this->collection->each(function ($term) use ($taxonomies) {
            $term
                ->blueprint($this->blueprint)
                ->columns($this->requestedColumns())
                ->taxonomies($taxonomies);
        });
    }

    public function with($request)
    {
        return [
            'meta' => [
                'columns' => $this->visibleColumns(),
            ],
        ];
    }
}
