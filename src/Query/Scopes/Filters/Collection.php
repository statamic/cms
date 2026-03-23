<?php

namespace Statamic\Query\Scopes\Filters;

use Statamic\Exceptions\AuthorizationException;
use Statamic\Facades;
use Statamic\Facades\User;
use Statamic\Query\Scopes\Filter;

class Collection extends Filter
{
    protected $pinned = true;

    public static function title()
    {
        return __('Collection');
    }

    public function fieldItems()
    {
        return [
            'collections' => [
                'placeholder' => __('Select Collection(s)'),
                'type' => 'select',
                'options' => $this->options()->all(),
                'clearable' => true,
                'multiple' => true,
            ],
        ];
    }

    public function apply($query, $values)
    {
        $this->authorizeCollectionAccess($values['collections']);

        $query->whereIn('collection', $values['collections']);
    }

    public function badge($values)
    {
        return __('Collections').': '.collect($values['collections'])->implode(', ');
    }

    public function visibleTo($key)
    {
        return $key === 'entries-fieldtype' && count($this->context['collections']) > 1;
    }

    protected function options()
    {
        $user = User::current();

        return collect($this->context['collections'])
            ->map(fn ($collection) => Facades\Collection::findByHandle($collection))
            ->filter(fn ($collection) => $collection && $user->can('view', $collection))
            ->mapWithKeys(fn ($collection) => [$collection->handle() => $collection->title()]);
    }

    private function authorizeCollectionAccess(array $collections): void
    {
        $user = User::current();

        collect($collections)->each(function (string $collectionHandle) use ($user) {
            $collection = Facades\Collection::findByHandle($collectionHandle);

            throw_if(
                ! $collection || ! $user->can('view', $collection),
                new AuthorizationException
            );
        });
    }
}
