<?php

namespace Statamic\Http\Resources\CP\Submissions;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

class Submissions extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ListedSubmission::class;
    protected $blueprint;
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

    private function setColumns()
    {
        $columns = $this->blueprint
            ->columns()
            ->ensurePrepended(Column::make('datestamp')->label('Date'));

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();
    }

    public function toArray($request)
    {
        $this->setColumns();

        return $this->collection->each(function ($collection) {
            $collection
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
