<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\Structure;
use Statamic\Structures\CollectionStructure;

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
                'id' => $this->getStructureId($structure),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Structure::all()->map(function ($structure) {
            return [
                'id' => $this->getStructureId($structure),
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

    private function getStructureId($structure)
    {
        $id = $structure->id();

        if ($structure instanceof CollectionStructure) {
            $id = 'collection::'.$id;
        }

        return $id;
    }
}
