<?php

namespace Statamic\Http\Resources\CP\Taxonomies;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

class Terms extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ListedTerm::class;
    protected $blueprint;
    protected $columnPreferenceKey;

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

    private function setColumns()
    {
        $columns = $this->blueprint->columns();

        $columns->push(
            Column::make()
                ->field('taxonomy')
                ->fieldtype('term')
                ->label(__('Taxonomy'))
                ->listable(true)
                ->defaultVisibility(true)
                ->visible(true)
                ->sortable(true)
                ->defaultOrder($columns->count() + 1)
        );

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();
    }

    public function toArray($request)
    {
        $this->setColumns();

        return $this->collection->each(function ($term) {
            $term
                ->blueprint($this->blueprint)
                ->columns($this->requestedColumns());
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
