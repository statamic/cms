<?php

namespace Statamic\Dictionaries;

use Symfony\Component\Intl\Currencies as SymfonyCurrencies;

class Currencies extends BasicDictionary
{
    protected string $valueKey = 'code';
    protected array $searchable = ['code', 'name', 'symbol'];
    protected array $keywords = ['currencies', 'currency', 'money', 'dollar'];

    protected function getItemLabel(array $item): string
    {
        return "{$item['name']} ({$item['code']})";
    }

    protected function getItems(): array
    {
        $locale = app()->getLocale();

        return collect(SymfonyCurrencies::getCurrencyCodes())
            ->map(function ($code) use ($locale) {
                $symbol = SymfonyCurrencies::getSymbol($code, $locale);

                return [
                    'code' => $code,
                    'name' => SymfonyCurrencies::getName($code, $locale),
                    'symbol' => $symbol === $code ? null : $symbol,
                    'decimals' => SymfonyCurrencies::getFractionDigits($code),
                ];
            })
            ->values()
            ->all();
    }
}
