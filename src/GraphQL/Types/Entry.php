<?php

namespace Statamic\GraphQL\Types;

use Facades\Statamic\GraphQL\TypeRepository;
use GraphQL\Type\Definition\Type;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Fields\Value;
use Statamic\Support\Str;

class Entry extends ObjectType
{
    public static function name(array $args): string
    {
        [$collection, $blueprint] = $args;

        return 'Entry_'.Str::studly($collection->handle()).'_'.Str::studly($blueprint->handle());
    }

    public function config(array $args): array
    {
        [$collection, $blueprint] = $args;

        return [
            'interfaces' => [
                TypeRepository::get(EntryInterface::class, $args),
            ],
            'fields' => function () use ($blueprint) {
                return $blueprint->fields()->toGraphQL()->merge([
                    'id' => Type::nonNull(Type::ID()),
                ])->all();
            },
            'resolveField' => function (EntryContract $entry, $args, $context, $info) {
                $value = $entry->augmentedValue($info->fieldName);

                if ($value instanceof Value) {
                    $value = $value->value();
                }

                return $value;
            },
        ];
    }
}
