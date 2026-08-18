<?php

namespace Statamic\Http\Resources\CP\Submissions;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

use function Statamic\trans as __;

class Submissions extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = ListedSubmission::class;
    protected $form;
    protected $columnPreferenceKey;
    protected $columns;

    public function form($form)
    {
        $this->form = $form;

        return $this;
    }

    public function columnPreferenceKey($key)
    {
        $this->columnPreferenceKey = $key;

        return $this;
    }

    private function setColumns()
    {
        $columns = $this->form
            ->blueprint()
            ->columns()
            ->when($this->form->hasUniqueInstances(), fn ($columns) => $columns->ensurePrepended(
                Column::make('entry')->label(__('Entry'))->fieldtype('relationship')->sortable(false)
            ))
            ->ensurePrepended(Column::make('datestamp')->label('Date'));

        $status = Column::make('status')
            ->label(__('Status'))
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

        return $this->collection->each(function ($collection) {
            $collection
                ->blueprint($this->form->blueprint())
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
