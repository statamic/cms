<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades\Structure;
use Statamic\Facades\User;
use Statamic\Structures\CollectionStructure;

class Structures extends Relationship
{
    protected $canEdit = false;
    protected $canCreate = false;
    protected $statusIcons = false;

    protected function authorizeItemData($id): bool
    {
        return User::current()->can('configure collections')
            || User::current()->can('configure navs');
    }

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
        throw_unless(
            User::current()->can('configure collections') || User::current()->can('configure navs'),
            new AuthorizationException
        );

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
