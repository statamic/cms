<?php

namespace Statamic\Fieldtypes;

use Statamic\CP\Column;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Nav;
use Statamic\GraphQL\Types\NavType;

class Navs extends Relationship
{
    protected $categories = ['relationship'];
    protected $canEdit = false;
    protected $canCreate = false;
    protected $canSearch = false;
    protected $statusIcons = false;
    protected $icon = 'structures';

    protected function toItemArray($id, $site = null)
    {
        if ($nav = Nav::findByHandle($id)) {
            return [
                'title' => $nav->title(),
                'id' => $nav->handle(),
            ];
        }

        return $this->invalidItemArray($id);
    }

    public function getIndexItems($request)
    {
        return Nav::all()->sortBy('title')->map(function ($nav) {
            return [
                'id' => $nav->handle(),
                'title' => $nav->title(),
            ];
        })->values();
    }

    protected function getColumns()
    {
        return [
            Column::make('title'),
        ];
    }

    protected function augmentValue($value)
    {
        return Nav::findByHandle($value);
    }

    public function toGqlType()
    {
        $type = GraphQL::type(NavType::NAME);

        if ($this->config('max_items') !== 1) {
            $type = GraphQL::listOf($type);
        }

        return $type;
    }
}
