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

    protected function configFieldItems(): array
    {
        return array_merge(parent::configFieldItems(), [
            [
                'display' => __('Structures'),
                'fields' => [
                    'structure_types' => [
                        'type' => 'checkboxes',
                        'display' => __('Structure Type'),
                        'options' => [
                            'collection' => __('Collection'),
                            'navigation' => __('Navigation'),
                        ],
                        'default' => [
                            'collection',
                            'navigation',
                        ],
                    ],
                ],
            ],
        ]);
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
        return Structure::all()
            ->when($this->config('structure_types', ['collection', 'navigation']), function ($structures, $structureTypes) {
                return $structures->filter(function ($structure) {
                    return $structure instanceof CollectionStructure
                        ? in_array('collection', $this->config('structure_types'))
                        : in_array('navigation', $this->config('structure_types'));
                });
            })
            ->map(function ($structure) {
                return [
                    'id' => $this->getStructureId($structure),
                    'title' => $structure->title(),
                ];
            })
            ->values();
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
