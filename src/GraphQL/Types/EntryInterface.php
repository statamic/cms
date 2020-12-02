<?php

namespace Statamic\GraphQL\Types;

use Facades\Statamic\GraphQL\TypeRepository;
use GraphQL\Type\Definition\Type;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\GraphQL\Types\Entry as EntryType;

class EntryInterface extends InterfaceType
{
    public static function name(): string
    {
        return 'EntryInterface';
    }

    public function __construct(array $config)
    {
        parent::__construct([
            'fields' => [
                'id' => Type::nonNull(Type::ID()),
                'title' => Type::nonNull(Type::string()),
            ],
            'resolveType' => function (EntryContract $entry) {
                return TypeRepository::get(EntryType::class);
            },
        ]);
    }

    public static function types(): array
    {
        return [Entry::name() => TypeRepository::get(Entry::class)];
    }
}
