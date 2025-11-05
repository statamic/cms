<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;

abstract class BasicDictionary extends Dictionary
{
    protected array $searchable = [];
    protected string $valueKey = 'value';
    protected string $labelKey = 'label';

    public function get(string $key): ?Item
    {
        return $this->collectItems()->get($key);
    }

    public function options(?string $search = null): array
    {
        return collect($this->optionItems($search))
            ->mapWithKeys(fn (Item $item) => [$item->value() => $item->label()])
            ->all();
    }

    public function optionItems(?string $search = null): array
    {
        return $this
            ->getFilteredItems()
            ->when($search, fn ($collection) => $collection->filter(fn ($item) => $this->matchesSearchQuery($search, $item)))
            ->all();
    }

    protected function getFilteredItems(): Collection
    {
        return $this->collectItems();
    }

    protected function collectItems(): Collection
    {
        return collect($this->getItems())->mapWithKeys(function ($arr) {
            $item = new Item($key = $this->getItemValue($arr), $this->getItemLabel($arr), $arr);

            return [$key => $item];
        });
    }

    protected function getItemValue(array $item): string
    {
        return $item[$this->valueKey];
    }

    protected function getItemLabel(array $item): string
    {
        return $item[$this->labelKey];
    }

    protected function matchesSearchQuery(string $query, Item $item): bool
    {
        $query = strtolower($query);

        foreach ($item->extra() as $key => $value) {
            if (! empty($this->searchable) && ! in_array($key, $this->searchable)) {
                continue;
            }

            if (str_contains(strtolower($value), $query)) {
                return true;
            }
        }

        return false;
    }

    abstract protected function getItems(): array;
}
