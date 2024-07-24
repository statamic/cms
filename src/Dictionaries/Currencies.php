<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;
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

    protected function getGqlFields(): array
    {
        return [
            ...parent::getGqlFields(),
            'decimals' => [
                'type' => GraphQL::nonNull(GraphQL::int()),
            ],
        ];
    }

    private function getCurrencies(): Collection
    {
        return collect([
            ['code' => 'AED', 'name' => 'United Arab Emirates Dirham', 'symbol' => "\u062f.\u0625.\u200f", 'decimals' => 2],
            ['code' => 'AFN', 'name' => 'Afghan Afghani', 'symbol' => "\u060b", 'decimals' => 0],
            ['code' => 'ALL', 'name' => 'Albanian Lek', 'symbol' => 'Lek', 'decimals' => 0],
            ['code' => 'AMD', 'name' => 'Armenian Dram', 'symbol' => "\u0564\u0580.", 'decimals' => 0],
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'AZN', 'name' => 'Azerbaijani Manat', 'symbol' => "\u043c\u0430\u043d.", 'decimals' => 2],
            ['code' => 'BAM', 'name' => 'Bosnia-Herzegovina Convertible Mark', 'symbol' => 'KM', 'decimals' => 2],
            ['code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => "\u09f3", 'decimals' => 2],
            ['code' => 'BGN', 'name' => 'Bulgarian Lev', 'symbol' => "\u043b\u0432.", 'decimals' => 2],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => "\u062f.\u0628.\u200f", 'decimals' => 3],
            ['code' => 'BIF', 'name' => 'Burundian Franc', 'symbol' => 'FBu', 'decimals' => 0],
            ['code' => 'BND', 'name' => 'Brunei Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'BOB', 'name' => 'Bolivian Boliviano', 'symbol' => 'Bs', 'decimals' => 2],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'decimals' => 2],
            ['code' => 'BWP', 'name' => 'Botswanan Pula', 'symbol' => 'P', 'decimals' => 2],
            ['code' => 'BYN', 'name' => 'Belarusian Ruble', 'symbol' => "\u0440\u0443\u0431.", 'decimals' => 2],
            ['code' => 'BZD', 'name' => 'Belize Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'CDF', 'name' => 'Congolese Franc', 'symbol' => 'FrCD', 'decimals' => 2],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'decimals' => 2],
            ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$', 'decimals' => 0],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => "CN\u00a5", 'decimals' => 2],
            ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => '$', 'decimals' => 0],
            ['code' => 'CRC', 'name' => "Costa Rican Col\u00f3n", 'symbol' => "\u20a1", 'decimals' => 0],
            ['code' => 'CVE', 'name' => 'Cape Verdean Escudo', 'symbol' => 'CV$', 'decimals' => 2],
            ['code' => 'CZK', 'name' => 'Czech Republic Koruna', 'symbol' => "K\u010d", 'decimals' => 2],
            ['code' => 'DJF', 'name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'decimals' => 0],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'DOP', 'name' => 'Dominican Peso', 'symbol' => 'RD$', 'decimals' => 2],
            ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => "\u062f.\u062c.\u200f", 'decimals' => 2],
            ['code' => 'EEK', 'name' => 'Estonian Kroon', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => "\u062c.\u0645.\u200f", 'decimals' => 2],
            ['code' => 'ERN', 'name' => 'Eritrean Nakfa', 'symbol' => 'Nfk', 'decimals' => 2],
            ['code' => 'ETB', 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'decimals' => 2],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => "\u20ac", 'decimals' => 2],
            ['code' => 'GBP', 'name' => 'British Pound Sterling', 'symbol' => "\u00a3", 'decimals' => 2],
            ['code' => 'GEL', 'name' => 'Georgian Lari', 'symbol' => 'GEL', 'decimals' => 2],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => "GH\u20b5", 'decimals' => 2],
            ['code' => 'GNF', 'name' => 'Guinean Franc', 'symbol' => 'FG', 'decimals' => 0],
            ['code' => 'GTQ', 'name' => 'Guatemalan Quetzal', 'symbol' => 'Q', 'decimals' => 2],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'HNL', 'name' => 'Honduran Lempira', 'symbol' => 'L', 'decimals' => 2],
            ['code' => 'HRK', 'name' => 'Croatian Kuna', 'symbol' => 'kn', 'decimals' => 2],
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'decimals' => 0],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'decimals' => 0],
            ['code' => 'ILS', 'name' => 'Israeli New Sheqel', 'symbol' => "\u20aa", 'decimals' => 2],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => "\u099f\u0995\u09be", 'decimals' => 2],
            ['code' => 'IQD', 'name' => 'Iraqi Dinar', 'symbol' => "\u062f.\u0639.\u200f", 'decimals' => 0],
            ['code' => 'IRR', 'name' => 'Iranian Rial', 'symbol' => "\ufdfc", 'decimals' => 0],
            ['code' => 'ISK', 'name' => "Icelandic Kr\u00f3na", 'symbol' => 'kr', 'decimals' => 0],
            ['code' => 'JMD', 'name' => 'Jamaican Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'JOD', 'name' => 'Jordanian Dinar', 'symbol' => "\u062f.\u0623.\u200f", 'decimals' => 3],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => "\uffe5", 'decimals' => 0],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'Ksh', 'decimals' => 2],
            ['code' => 'KHR', 'name' => 'Cambodian Riel', 'symbol' => "\u17db", 'decimals' => 2],
            ['code' => 'KMF', 'name' => 'Comorian Franc', 'symbol' => 'FC', 'decimals' => 0],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => "\u20a9", 'decimals' => 0],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => "\u062f.\u0643.\u200f", 'decimals' => 3],
            ['code' => 'KZT', 'name' => 'Kazakhstani Tenge', 'symbol' => "\u0442\u04a3\u0433.", 'decimals' => 2],
            ['code' => 'LBP', 'name' => 'Lebanese Pound', 'symbol' => "\u0644.\u0644.\u200f", 'decimals' => 0],
            ['code' => 'LKR', 'name' => 'Sri Lankan Rupee', 'symbol' => 'SL Re', 'decimals' => 2],
            ['code' => 'LTL', 'name' => 'Lithuanian Litas', 'symbol' => 'Lt', 'decimals' => 2],
            ['code' => 'LVL', 'name' => 'Latvian Lats', 'symbol' => 'Ls', 'decimals' => 2],
            ['code' => 'LYD', 'name' => 'Libyan Dinar', 'symbol' => "\u062f.\u0644.\u200f", 'decimals' => 3],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => "\u062f.\u0645.\u200f", 'decimals' => 2],
            ['code' => 'MDL', 'name' => 'Moldovan Leu', 'symbol' => 'MDL', 'decimals' => 2],
            ['code' => 'MGA', 'name' => 'Malagasy Ariary', 'symbol' => 'MGA', 'decimals' => 0],
            ['code' => 'MKD', 'name' => 'Macedonian Denar', 'symbol' => 'MKD', 'decimals' => 2],
            ['code' => 'MMK', 'name' => 'Myanma Kyat', 'symbol' => 'K', 'decimals' => 0],
            ['code' => 'MOP', 'name' => 'Macanese Pataca', 'symbol' => 'MOP$', 'decimals' => 2],
            ['code' => 'MUR', 'name' => 'Mauritian Rupee', 'symbol' => 'MURs', 'decimals' => 0],
            ['code' => 'MXN', 'name' => 'Mexican Peso', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'MYR', 'name' => 'Malaysian Ringgit', 'symbol' => 'RM', 'decimals' => 2],
            ['code' => 'MZN', 'name' => 'Mozambican Metical', 'symbol' => 'MTn', 'decimals' => 2],
            ['code' => 'NAD', 'name' => 'Namibian Dollar', 'symbol' => 'N$', 'decimals' => 2],
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => "\u20a6", 'decimals' => 2],
            ['code' => 'NIO', 'name' => "Nicaraguan C\u00f3rdoba", 'symbol' => 'C$', 'decimals' => 2],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'NPR', 'name' => 'Nepalese Rupee', 'symbol' => "\u0928\u0947\u0930\u0942", 'decimals' => 2],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => "\u0631.\u0639.\u200f", 'decimals' => 3],
            ['code' => 'PAB', 'name' => 'Panamanian Balboa', 'symbol' => "B\/.", 'decimals' => 2],
            ['code' => 'PEN', 'name' => 'Peruvian Nuevo Sol', 'symbol' => "S\/.", 'decimals' => 2],
            ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => "\u20b1", 'decimals' => 2],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => "\u20a8", 'decimals' => 0],
            ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => "z\u0142", 'decimals' => 2],
            ['code' => 'PYG', 'name' => 'Paraguayan Guarani', 'symbol' => "\u20b2", 'decimals' => 0],
            ['code' => 'QAR', 'name' => 'Qatari Rial', 'symbol' => "\u0631.\u0642.\u200f", 'decimals' => 2],
            ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'RON', 'decimals' => 2],
            ['code' => 'RSD', 'name' => 'Serbian Dinar', 'symbol' => "\u0434\u0438\u043d.", 'decimals' => 0],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => "\u20bd.", 'decimals' => 2],
            ['code' => 'RWF', 'name' => 'Rwandan Franc', 'symbol' => 'FR', 'decimals' => 0],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => "\u0631.\u0633.\u200f", 'decimals' => 2],
            ['code' => 'SDG', 'name' => 'Sudanese Pound', 'symbol' => 'SDG', 'decimals' => 2],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'SOS', 'name' => 'Somali Shilling', 'symbol' => 'Ssh', 'decimals' => 0],
            ['code' => 'SYP', 'name' => 'Syrian Pound', 'symbol' => "\u0644.\u0633.\u200f", 'decimals' => 0],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => "\u0e3f", 'decimals' => 2],
            ['code' => 'TND', 'name' => 'Tunisian Dinar', 'symbol' => "\u062f.\u062a.\u200f", 'decimals' => 3],
            ['code' => 'TOP', 'name' => "Tongan Pa\u02bbanga", 'symbol' => 'T$', 'decimals' => 2],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => 'TL', 'decimals' => 2],
            ['code' => 'TTD', 'name' => 'Trinidad and Tobago Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'TWD', 'name' => 'New Taiwan Dollar', 'symbol' => 'NT$', 'decimals' => 2],
            ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'decimals' => 0],
            ['code' => 'UAH', 'name' => 'Ukrainian Hryvnia', 'symbol' => "\u20b4", 'decimals' => 2],
            ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'decimals' => 0],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'UYU', 'name' => 'Uruguayan Peso', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'UZS', 'name' => 'Uzbekistan Som', 'symbol' => 'UZS', 'decimals' => 0],
            ['code' => 'VEF', 'name' => "Venezuelan Bol\u00edvar", 'symbol' => 'Bs.F.', 'decimals' => 2],
            ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => "\u20ab", 'decimals' => 0],
            ['code' => 'XAF', 'name' => 'CFA Franc BEAC', 'symbol' => 'FCFA', 'decimals' => 0],
            ['code' => 'XOF', 'name' => 'CFA Franc BCEAO', 'symbol' => 'CFA', 'decimals' => 0],
            ['code' => 'YER', 'name' => 'Yemeni Rial', 'symbol' => "\u0631.\u064a.\u200f", 'decimals' => 0],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'decimals' => 2],
            ['code' => 'ZMK', 'name' => 'Zambian Kwacha', 'symbol' => 'ZK', 'decimals' => 0],
            ['code' => 'ZWL', 'name' => 'Zimbabwean Dollar', 'symbol' => 'ZWL$', 'decimals' => 0],
        ]);
    }
}
