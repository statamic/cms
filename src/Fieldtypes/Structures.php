<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\Structure;

class Structures extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $statusIcons = false;

    protected function toItemArray($id)
    {
        if ($structure = Structure::find($id)) {
            return [
                'title' => $structure->title(),
                'id' => $structure->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Structure::all()->map(function ($structure) {
            return [
                'id' => $structure->handle(),
                'title' => $structure->title(),
            ];
        })->values();
    }

    public function augmentValue($value)
    {
        return Structure::find($value);
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }
}
