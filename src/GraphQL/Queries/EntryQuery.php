<?php

namespace Statamic\GraphQL\Queries;

use GraphQL\Type\Definition\Type;
use Statamic\Facades;
use Statamic\Facades\GraphQL;
use Statamic\GraphQL\Types\EntryInterface;

class EntryQuery extends Query
{
    protected $attributes = [
        'name' => 'entry',
    ];

    public function type(): Type
    {
        return GraphQL::type(EntryInterface::NAME);
    }

    public function args(): array
    {
        return [
            'id' => GraphQL::string(),
        ];
    }

    public function resolve($root, $args)
    {
        $query = Facades\Entry::query();

        if ($id = $args['id']) {
            $query->where('id', $id);
        }

        return $query->limit(1)->get()->first();
    }
}
