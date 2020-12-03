<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\GraphQL\TypeRepository;
use GraphQL\Type\Definition\Type;
use Statamic\Facades;
use Statamic\GraphQL\Types\EntryInterface;

class Entry
{
    public static function definition(): array
    {
        EntryInterface::registerTypes();

        return [
            'type' => TypeRepository::get(EntryInterface::class),
            'args' => [
                'id' => Type::string(),
            ],
            'resolve' => function ($value, $args) {
                $query = Facades\Entry::query();

                if ($id = $args['id']) {
                    $query->where('id', $id);
                }

                return $query->limit(1)->get()->first();
            },
        ];
    }
}
