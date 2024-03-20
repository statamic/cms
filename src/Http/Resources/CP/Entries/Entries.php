<?php

namespace Statamic\Http\Resources\CP\Entries;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

class Entries extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ListedEntry::class;
    protected $blueprint;
    protected $columns;
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

        $status = Column::make('status')
            ->listable(true)
            ->visible(true)
            ->defaultVisibility(true)
            ->defaultOrder($columns->count() + 1)
            ->sortable(false);

        $columns->put('status', $status);

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();
    }

    public function toArray($request)
    {
        $this->setColumns();

        return $this->collection->each(function ($entry) {
            $entry
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
