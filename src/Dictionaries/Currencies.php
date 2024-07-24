<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\GraphQL;

class Currencies extends Dictionary
{
    public function options(?string $search = null): array
    {
        return $this->getCurrencies()
            ->when($search ?? false, function ($collection) use ($search) {
                return $collection->filter(function (array $currency) use ($search) {
                    return str_contains(strtolower($currency['name']), strtolower($search))
                        || str_contains(strtolower($currency['code']), strtolower($search))
                        || $currency['symbol'] === $search;
                });
            })
            ->mapWithKeys(function (array $currency) {
                return [$currency['code'] => "{$currency['name']} ({$currency['code']})"];
            })
            ->all();
    }

    public function get(string $key): array
    {
        return $this->getCurrencies()->firstWhere('code', $key);
    }

    private function getCurrencies(): Collection
    {
        return collect(json_decode(File::get(__DIR__.'/../../resources/dictionaries/currencies.json'), true));
    }

    protected function getGqlFields(): array
    {
        return [
            ...parent::getGqlFields(),
            'decimal_digits' => [
                'type' => GraphQL::nonNull(GraphQL::int()),
            ],
        ];
    }
}
