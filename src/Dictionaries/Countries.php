<?php

namespace Statamic\Dictionaries;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\Intl\Countries as SymfonyCountries;

class Countries extends BasicDictionary
{
    protected string $valueKey = 'iso3';
    protected array $searchable = ['name', 'iso3'];
    protected array $keywords = ['countries', 'country'];
    private array $regions;
    private array $subregions;

    private array $subregionToRegion = [
        'australia_new_zealand' => 'oceania',
        'caribbean' => 'americas',
        'central_africa' => 'africa',
        'central_america' => 'americas',
        'central_asia' => 'asia',
        'eastern_africa' => 'africa',
        'eastern_asia' => 'asia',
        'eastern_europe' => 'europe',
        'melanesia' => 'oceania',
        'micronesia' => 'oceania',
        'northern_africa' => 'africa',
        'northern_america' => 'americas',
        'northern_europe' => 'europe',
        'polynesia' => 'oceania',
        'southern_africa' => 'africa',
        'southern_asia' => 'asia',
        'southern_europe' => 'europe',
        'south_america' => 'americas',
        'south_eastern_asia' => 'asia',
        'western_africa' => 'africa',
        'western_asia' => 'asia',
        'western_europe' => 'europe',
    ];

    private array $subregionCountries = [
        // Africa
        'northern_africa' => ['DZ', 'EG', 'EH', 'LY', 'MA', 'SD', 'TN'],
        'central_africa' => ['AO', 'CD', 'CF', 'CG', 'CM', 'GA', 'GQ', 'SS', 'ST', 'TD'],
        'eastern_africa' => [
            'BI', 'DJ', 'ER', 'ET', 'IO', 'KE', 'KM', 'MG', 'MU', 'MW',
            'MZ', 'RE', 'RW', 'SC', 'SO', 'TZ', 'UG', 'YT', 'ZM', 'ZW',
        ],
        'southern_africa' => ['BW', 'LS', 'NA', 'SZ', 'TF', 'ZA'],
        'western_africa' => [
            'BF', 'BJ', 'CI', 'CV', 'GH', 'GM', 'GN', 'GW', 'LR',
            'ML', 'MR', 'NE', 'NG', 'SH', 'SL', 'SN', 'TG',
        ],

        // Americas
        'caribbean' => [
            'AG', 'AI', 'AW', 'BB', 'BL', 'BQ', 'BS', 'CU', 'CW', 'DM',
            'DO', 'GD', 'GP', 'HT', 'JM', 'KN', 'KY', 'LC', 'MF', 'MQ',
            'MS', 'PR', 'SX', 'TC', 'TT', 'VC', 'VG', 'VI',
        ],
        'central_america' => ['BZ', 'CR', 'GT', 'HN', 'MX', 'NI', 'PA', 'SV'],
        'northern_america' => ['BM', 'CA', 'GL', 'PM', 'UM', 'US'],
        'south_america' => [
            'AR', 'BO', 'BR', 'CL', 'CO', 'EC', 'FK', 'GF',
            'GS', 'GY', 'PE', 'PY', 'SR', 'UY', 'VE',
        ],

        // Asia
        'central_asia' => ['KG', 'KZ', 'TJ', 'TM', 'UZ'],
        'eastern_asia' => ['CN', 'HK', 'JP', 'KP', 'KR', 'MN', 'MO', 'TW'],
        'southern_asia' => ['AF', 'BD', 'BT', 'IN', 'IR', 'LK', 'MV', 'NP', 'PK'],
        'south_eastern_asia' => ['BN', 'ID', 'KH', 'LA', 'MM', 'MY', 'PH', 'SG', 'TH', 'TL', 'VN'],
        'western_asia' => [
            'AE', 'AM', 'AZ', 'BH', 'GE', 'IL', 'IQ', 'JO', 'KW',
            'LB', 'OM', 'PS', 'QA', 'SA', 'SY', 'TR', 'YE',
        ],

        // Europe
        'eastern_europe' => ['BG', 'BY', 'CZ', 'HU', 'MD', 'PL', 'RO', 'RU', 'SK', 'UA'],
        'northern_europe' => [
            'AX', 'DK', 'EE', 'FI', 'FO', 'GB', 'GG', 'IE',
            'IM', 'IS', 'JE', 'LT', 'LV', 'NO', 'SE', 'SJ',
        ],
        'southern_europe' => [
            'AD', 'AL', 'BA', 'CY', 'ES', 'GI', 'GR', 'HR', 'IT',
            'ME', 'MK', 'MT', 'PT', 'RS', 'SI', 'SM', 'VA',
        ],
        'western_europe' => ['AT', 'BE', 'CH', 'DE', 'FR', 'LI', 'LU', 'MC', 'NL'],

        // Oceania
        'australia_new_zealand' => ['AU', 'CC', 'CX', 'NF', 'NZ'],
        'melanesia' => ['FJ', 'NC', 'PG', 'SB', 'VU'],
        'micronesia' => ['FM', 'GU', 'KI', 'MH', 'MP', 'NR', 'PW'],
        'polynesia' => ['AS', 'CK', 'NU', 'PF', 'PN', 'TK', 'TO', 'TV', 'WF', 'WS'],
    ];

    public function __construct()
    {
        $this->regions = [
            'africa' => __('statamic::dictionary-countries.regions.africa'),
            'americas' => __('statamic::dictionary-countries.regions.americas'),
            'asia' => __('statamic::dictionary-countries.regions.asia'),
            'europe' => __('statamic::dictionary-countries.regions.europe'),
            'oceania' => __('statamic::dictionary-countries.regions.oceania'),
        ];

        $this->subregions = [
            'australia_new_zealand' => __('statamic::dictionary-countries.subregions.australia_new_zealand'),
            'caribbean' => __('statamic::dictionary-countries.subregions.caribbean'),
            'central_africa' => __('statamic::dictionary-countries.subregions.central_africa'),
            'central_america' => __('statamic::dictionary-countries.subregions.central_america'),
            'central_asia' => __('statamic::dictionary-countries.subregions.central_asia'),
            'eastern_africa' => __('statamic::dictionary-countries.subregions.eastern_africa'),
            'eastern_asia' => __('statamic::dictionary-countries.subregions.eastern_asia'),
            'eastern_europe' => __('statamic::dictionary-countries.subregions.eastern_europe'),
            'melanesia' => __('statamic::dictionary-countries.subregions.melanesia'),
            'micronesia' => __('statamic::dictionary-countries.subregions.micronesia'),
            'northern_africa' => __('statamic::dictionary-countries.subregions.northern_africa'),
            'northern_america' => __('statamic::dictionary-countries.subregions.northern_america'),
            'northern_europe' => __('statamic::dictionary-countries.subregions.northern_europe'),
            'polynesia' => __('statamic::dictionary-countries.subregions.polynesia'),
            'southern_africa' => __('statamic::dictionary-countries.subregions.southern_africa'),
            'southern_asia' => __('statamic::dictionary-countries.subregions.southern_asia'),
            'southern_europe' => __('statamic::dictionary-countries.subregions.southern_europe'),
            'south_america' => __('statamic::dictionary-countries.subregions.south_america'),
            'south_eastern_asia' => __('statamic::dictionary-countries.subregions.south_eastern_asia'),
            'western_africa' => __('statamic::dictionary-countries.subregions.western_africa'),
            'western_asia' => __('statamic::dictionary-countries.subregions.western_asia'),
            'western_europe' => __('statamic::dictionary-countries.subregions.western_europe'),
        ];
    }

    protected function getItemLabel(array $item): string
    {
        return vsprintf('%s%s', [
            ($this->config['emojis'] ?? true) ? "{$item['emoji']} " : '',
            $item['name'],
        ]);
    }

    protected function fieldItems()
    {
        return [
            'region' => [
                'display' => __('Region'),
                'instructions' => __('statamic::messages.dictionaries_countries_region_instructions'),
                'type' => 'select',
                'clearable' => true,
                'options' => $this->regions,
                'width' => 50,
            ],
            'emojis' => [
                'display' => __('Emojis'),
                'instructions' => __('statamic::messages.dictionaries_countries_emojis_instructions'),
                'type' => 'toggle',
                'default' => true,
                'width' => 50,
            ],
        ];
    }

    protected function getFilteredItems(): Collection
    {
        return $this
            ->collectItems()
            ->when($this->config['region'] ?? false, fn ($collection, $region) => $collection->where('region', $this->regions[$region]));
    }

    protected function getItems(): array
    {
        $locale = app()->getLocale();
        $subregionMap = collect($this->subregionCountries)
            ->flatMap(fn ($iso2s, $subregion) => array_fill_keys($iso2s, $subregion))
            ->all();

        return collect(SymfonyCountries::getCountryCodes())
            ->map(function ($iso2) use ($locale, $subregionMap) {
                $subregion = $subregionMap[$iso2] ?? null;

                return [
                    'name' => SymfonyCountries::getName($iso2, $locale),
                    'iso3' => SymfonyCountries::getAlpha3Code($iso2),
                    'iso2' => $iso2,
                    'region' => $subregion ? $this->regions[$this->subregionToRegion[$subregion]] : '',
                    'subregion' => $subregion ? $this->subregions[$subregion] : '',
                    'emoji' => $this->emojiFor($iso2),
                ];
            })
            ->sortBy(fn ($item) => Str::ascii($item['name']), SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    private function emojiFor(string $iso2): string
    {
        return mb_chr(0x1F1E6 + ord($iso2[0]) - ord('A'))
            .mb_chr(0x1F1E6 + ord($iso2[1]) - ord('A'));
    }
}
