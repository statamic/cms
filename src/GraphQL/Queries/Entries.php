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
            'resolve' => function ($value, $args) {
                return Entry::all();
            },
        ];
    }
}
