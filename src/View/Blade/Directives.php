<?php

namespace Statamic\View\Blade;

use Statamic\Facades\Collection as CollectionAPI;
use Statamic\Stache\Query\EntryQueryBuilder;
use Statamic\Support\Arr;

class Directives
{
    private EntryQueryBuilder $collectionQuery;
    private array $params;

    public function collection(string $handle, array $params = [])
    {
        $this->params = $params;
        $this->collectionQuery = CollectionAPI::find($handle)->queryEntries();

        $this->filter();
        $this->limit();
        $this->orderBy();

        return $this->collectionQuery->get()->toAugmentedArray();
    }

    private function filter()
    {
        if ($where = Arr::get($this->params, 'where')) {
            foreach (explode(',', $where) as $condition) {
                list($field, $value) = explode(':', $condition);

                $this->collectionQuery->where(trim($field), trim($value));
            }
        }
    }

    private function limit()
    {
        if ($limit = Arr::get($this->params, 'limit')) {
            $this->collectionQuery->limit($limit);
        }
    }

    private function orderBy()
    {
        if ($orderBy = Arr::get($this->params, 'orderBy')) {
            $sort = explode(':', $orderBy);
            $field = $sort[0];
            $direction = $sort[1] ?? 'asc';

            $this->collectionQuery->orderBy($field, $direction);
        }
    }
}
