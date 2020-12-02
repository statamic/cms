<?php

namespace Statamic\GraphQL\Types;

use Facades\Statamic\GraphQL\TypeRepository;
use GraphQL\Type\Definition\Type;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Collection;
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
                return TypeRepository::get(EntryType::class, [$entry->collection(), $entry->blueprint()]);
            },
        ]);
    }

    public static function types(): array
    {
        return Collection::all()
            ->flatMap(function ($collection) {
                return $collection->entryBlueprints()->map(function ($blueprint) use ($collection) {
                    return compact('collection', 'blueprint');
                });
            })
            ->mapWithKeys(function ($item) {
                $type = TypeRepository::get(EntryType::class, [$item['collection'], $item['blueprint']]);

                return [$type->name => $type];
            })
            ->all();
    }
}
