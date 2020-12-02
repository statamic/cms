<?php

namespace Statamic\GraphQL\Types;

use Facades\Statamic\GraphQL\TypeRepository;
use GraphQL\Type\Definition\Type;
use Statamic\Contracts\Entries\Entry as EntryContract;

class Entry extends ObjectType
{
    public static function name(): string
    {
        return 'Entry';
    }

    public function config(array $args): array
    {
        return [
            'interfaces' => [
                TypeRepository::get(EntryInterface::class),
            ],
            'fields' => function () {
                return [
                    'id' => Type::nonNull(Type::ID()),
                    'title' => Type::nonNull(Type::string()),
                ];
            },
            'resolveField' => function (EntryContract $entry, $args, $context, $info) {
                return $entry->augmentedValue($info->fieldName);
            },
        ];
    }
}
