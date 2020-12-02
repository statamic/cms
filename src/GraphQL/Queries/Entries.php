<?php

namespace Statamic\GraphQL\Queries;

use Facades\Statamic\GraphQL\TypeRepository;
use GraphQL\Type\Definition\Type;
use Statamic\Facades\Entry;
use Statamic\GraphQL\Types\EntryInterface;

class Entries
{
    public static function definition(): array
    {
        EntryInterface::registerTypes();

        return [
            'type' => Type::listOf(TypeRepository::get(EntryInterface::class)),
            'args' => [
                'collection' => Type::listOf(Type::string()),
            ],
            'resolve' => function ($value, $args) {
                $query = Entry::query();

                if ($collection = $args['collection'] ?? null) {
                    $query->whereIn('collection', $collection);
                }

                return $query->get();
            },
        ];
    }
}
