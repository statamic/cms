<?php

namespace Statamic\Http\Resources\CP\Assets;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Statamic\CP\Column;
use Statamic\Http\Resources\CP\Concerns\HasRequestedColumns;

class SearchedAssetsCollection extends ResourceCollection
{
    use HasRequestedColumns;

    public $collects = FolderAsset::class;
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

    public function setColumns()
    {
        $columns = $this->blueprint->columns();

        $basename = Column::make('basename')
            ->label(__('File'))
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(true)
            ->required(true);

        $size = Column::make('size')
            ->label(__('Size'))
            ->value('size_formatted')
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(true);

        $lastModified = Column::make('last_modified')
            ->label(__('Last Modified'))
            ->value('last_modified_relative')
            ->visible(true)
            ->defaultVisibility(true)
            ->sortable(true);

        $columns->put('basename', $basename);
        $columns->put('size', $size);
        $columns->put('last_modified', $lastModified);

        if ($key = $this->columnPreferenceKey) {
            $columns->setPreferred($key);
        }

        $this->columns = $columns->rejectUnlisted()->values();
    }

    public function toArray($request)
    {
        $this->setColumns();

        return [
            'assets' => $this->collection->each(function ($asset) {
                $asset
                    ->blueprint($this->blueprint)
                    ->columns($this->columns);
            }),
        ];
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
