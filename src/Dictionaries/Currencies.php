<?php

namespace Statamic\Dictionaries;

use Statamic\Facades\GraphQL;

class Currencies extends BasicDictionary
{
    protected string $valueKey = 'code';

    protected function getItemLabel(array $item): string
    {
        return "{$item['name']} ({$item['code']})";
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

    protected function getItems(): array
    {
        return [
            ['code' => 'AED', 'name' => 'United Arab Emirates Dirham', 'symbol' => 'د.إ.‏', 'decimals' => 2],
            ['code' => 'AFN', 'name' => 'Afghan Afghani', 'symbol' => '؋', 'decimals' => 0],
            ['code' => 'ALL', 'name' => 'Albanian Lek', 'symbol' => 'Lek', 'decimals' => 0],
            ['code' => 'AMD', 'name' => 'Armenian Dram', 'symbol' => 'դր.', 'decimals' => 0],
            ['code' => 'ARS', 'name' => 'Argentine Peso', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'AUD', 'name' => 'Australian Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'AZN', 'name' => 'Azerbaijani Manat', 'symbol' => 'ман.', 'decimals' => 2],
            ['code' => 'BAM', 'name' => 'Bosnia-Herzegovina Convertible Mark', 'symbol' => 'KM', 'decimals' => 2],
            ['code' => 'BDT', 'name' => 'Bangladeshi Taka', 'symbol' => '৳', 'decimals' => 2],
            ['code' => 'BGN', 'name' => 'Bulgarian Lev', 'symbol' => 'лв.', 'decimals' => 2],
            ['code' => 'BHD', 'name' => 'Bahraini Dinar', 'symbol' => 'د.ب.‏', 'decimals' => 3],
            ['code' => 'BIF', 'name' => 'Burundian Franc', 'symbol' => 'FBu', 'decimals' => 0],
            ['code' => 'BND', 'name' => 'Brunei Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'BOB', 'name' => 'Bolivian Boliviano', 'symbol' => 'Bs', 'decimals' => 2],
            ['code' => 'BRL', 'name' => 'Brazilian Real', 'symbol' => 'R$', 'decimals' => 2],
            ['code' => 'BWP', 'name' => 'Botswanan Pula', 'symbol' => 'P', 'decimals' => 2],
            ['code' => 'BYN', 'name' => 'Belarusian Ruble', 'symbol' => 'руб.', 'decimals' => 2],
            ['code' => 'BZD', 'name' => 'Belize Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'CAD', 'name' => 'Canadian Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'CDF', 'name' => 'Congolese Franc', 'symbol' => 'FrCD', 'decimals' => 2],
            ['code' => 'CHF', 'name' => 'Swiss Franc', 'symbol' => 'CHF', 'decimals' => 2],
            ['code' => 'CLP', 'name' => 'Chilean Peso', 'symbol' => '$', 'decimals' => 0],
            ['code' => 'CNY', 'name' => 'Chinese Yuan', 'symbol' => 'CN¥', 'decimals' => 2],
            ['code' => 'COP', 'name' => 'Colombian Peso', 'symbol' => '$', 'decimals' => 0],
            ['code' => 'CRC', 'name' => "Costa Rican Col\u00f3n", 'symbol' => '₡', 'decimals' => 0],
            ['code' => 'CVE', 'name' => 'Cape Verdean Escudo', 'symbol' => 'CV$', 'decimals' => 2],
            ['code' => 'CZK', 'name' => 'Czech Republic Koruna', 'symbol' => 'Kč', 'decimals' => 2],
            ['code' => 'DJF', 'name' => 'Djiboutian Franc', 'symbol' => 'Fdj', 'decimals' => 0],
            ['code' => 'DKK', 'name' => 'Danish Krone', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'DOP', 'name' => 'Dominican Peso', 'symbol' => 'RD$', 'decimals' => 2],
            ['code' => 'DZD', 'name' => 'Algerian Dinar', 'symbol' => 'د.ج.‏', 'decimals' => 2],
            ['code' => 'EEK', 'name' => 'Estonian Kroon', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'EGP', 'name' => 'Egyptian Pound', 'symbol' => 'ج.م.‏', 'decimals' => 2],
            ['code' => 'ERN', 'name' => 'Eritrean Nakfa', 'symbol' => 'Nfk', 'decimals' => 2],
            ['code' => 'ETB', 'name' => 'Ethiopian Birr', 'symbol' => 'Br', 'decimals' => 2],
            ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€', 'decimals' => 2],
            ['code' => 'GBP', 'name' => 'British Pound Sterling', 'symbol' => '£', 'decimals' => 2],
            ['code' => 'GEL', 'name' => 'Georgian Lari', 'symbol' => 'GEL', 'decimals' => 2],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'decimals' => 2],
            ['code' => 'GNF', 'name' => 'Guinean Franc', 'symbol' => 'FG', 'decimals' => 0],
            ['code' => 'GTQ', 'name' => 'Guatemalan Quetzal', 'symbol' => 'Q', 'decimals' => 2],
            ['code' => 'HKD', 'name' => 'Hong Kong Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'HNL', 'name' => 'Honduran Lempira', 'symbol' => 'L', 'decimals' => 2],
            ['code' => 'HRK', 'name' => 'Croatian Kuna', 'symbol' => 'kn', 'decimals' => 2],
            ['code' => 'HUF', 'name' => 'Hungarian Forint', 'symbol' => 'Ft', 'decimals' => 0],
            ['code' => 'IDR', 'name' => 'Indonesian Rupiah', 'symbol' => 'Rp', 'decimals' => 0],
            ['code' => 'ILS', 'name' => 'Israeli New Sheqel', 'symbol' => '₪', 'decimals' => 2],
            ['code' => 'INR', 'name' => 'Indian Rupee', 'symbol' => 'টকা', 'decimals' => 2],
            ['code' => 'IQD', 'name' => 'Iraqi Dinar', 'symbol' => 'د.ع.‏', 'decimals' => 0],
            ['code' => 'IRR', 'name' => 'Iranian Rial', 'symbol' => '﷼', 'decimals' => 0],
            ['code' => 'ISK', 'name' => "Icelandic Kr\u00f3na", 'symbol' => 'kr', 'decimals' => 0],
            ['code' => 'JMD', 'name' => 'Jamaican Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'JOD', 'name' => 'Jordanian Dinar', 'symbol' => 'د.أ.‏', 'decimals' => 3],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'symbol' => '￥', 'decimals' => 0],
            ['code' => 'KES', 'name' => 'Kenyan Shilling', 'symbol' => 'Ksh', 'decimals' => 2],
            ['code' => 'KHR', 'name' => 'Cambodian Riel', 'symbol' => '៛', 'decimals' => 2],
            ['code' => 'KMF', 'name' => 'Comorian Franc', 'symbol' => 'FC', 'decimals' => 0],
            ['code' => 'KRW', 'name' => 'South Korean Won', 'symbol' => '₩', 'decimals' => 0],
            ['code' => 'KWD', 'name' => 'Kuwaiti Dinar', 'symbol' => 'د.ك.‏', 'decimals' => 3],
            ['code' => 'KZT', 'name' => 'Kazakhstani Tenge', 'symbol' => 'тңг.', 'decimals' => 2],
            ['code' => 'LBP', 'name' => 'Lebanese Pound', 'symbol' => 'ل.ل.‏', 'decimals' => 0],
            ['code' => 'LKR', 'name' => 'Sri Lankan Rupee', 'symbol' => 'SL Re', 'decimals' => 2],
            ['code' => 'LTL', 'name' => 'Lithuanian Litas', 'symbol' => 'Lt', 'decimals' => 2],
            ['code' => 'LVL', 'name' => 'Latvian Lats', 'symbol' => 'Ls', 'decimals' => 2],
            ['code' => 'LYD', 'name' => 'Libyan Dinar', 'symbol' => 'د.ل.‏', 'decimals' => 3],
            ['code' => 'MAD', 'name' => 'Moroccan Dirham', 'symbol' => 'د.م.‏', 'decimals' => 2],
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
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'decimals' => 2],
            ['code' => 'NIO', 'name' => "Nicaraguan C\u00f3rdoba", 'symbol' => 'C$', 'decimals' => 2],
            ['code' => 'NOK', 'name' => 'Norwegian Krone', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'NPR', 'name' => 'Nepalese Rupee', 'symbol' => 'नेरू', 'decimals' => 2],
            ['code' => 'NZD', 'name' => 'New Zealand Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'OMR', 'name' => 'Omani Rial', 'symbol' => 'ر.ع.‏', 'decimals' => 3],
            ['code' => 'PAB', 'name' => 'Panamanian Balboa', 'symbol' => 'B/.', 'decimals' => 2],
            ['code' => 'PEN', 'name' => 'Peruvian Nuevo Sol', 'symbol' => 'S/.', 'decimals' => 2],
            ['code' => 'PHP', 'name' => 'Philippine Peso', 'symbol' => '₱', 'decimals' => 2],
            ['code' => 'PKR', 'name' => 'Pakistani Rupee', 'symbol' => '₨', 'decimals' => 0],
            ['code' => 'PLN', 'name' => 'Polish Zloty', 'symbol' => 'zł', 'decimals' => 2],
            ['code' => 'PYG', 'name' => 'Paraguayan Guarani', 'symbol' => '₲', 'decimals' => 0],
            ['code' => 'QAR', 'name' => 'Qatari Rial', 'symbol' => 'ر.ق.‏', 'decimals' => 2],
            ['code' => 'RON', 'name' => 'Romanian Leu', 'symbol' => 'RON', 'decimals' => 2],
            ['code' => 'RSD', 'name' => 'Serbian Dinar', 'symbol' => 'дин.', 'decimals' => 0],
            ['code' => 'RUB', 'name' => 'Russian Ruble', 'symbol' => '₽.', 'decimals' => 2],
            ['code' => 'RWF', 'name' => 'Rwandan Franc', 'symbol' => 'FR', 'decimals' => 0],
            ['code' => 'SAR', 'name' => 'Saudi Riyal', 'symbol' => 'ر.س.‏', 'decimals' => 2],
            ['code' => 'SDG', 'name' => 'Sudanese Pound', 'symbol' => 'SDG', 'decimals' => 2],
            ['code' => 'SEK', 'name' => 'Swedish Krona', 'symbol' => 'kr', 'decimals' => 2],
            ['code' => 'SGD', 'name' => 'Singapore Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'SOS', 'name' => 'Somali Shilling', 'symbol' => 'Ssh', 'decimals' => 0],
            ['code' => 'SYP', 'name' => 'Syrian Pound', 'symbol' => 'ل.س.‏', 'decimals' => 0],
            ['code' => 'THB', 'name' => 'Thai Baht', 'symbol' => '฿', 'decimals' => 2],
            ['code' => 'TND', 'name' => 'Tunisian Dinar', 'symbol' => 'د.ت.‏', 'decimals' => 3],
            ['code' => 'TOP', 'name' => "Tongan Pa\u02bbanga", 'symbol' => 'T$', 'decimals' => 2],
            ['code' => 'TRY', 'name' => 'Turkish Lira', 'symbol' => 'TL', 'decimals' => 2],
            ['code' => 'TTD', 'name' => 'Trinidad and Tobago Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'TWD', 'name' => 'New Taiwan Dollar', 'symbol' => 'NT$', 'decimals' => 2],
            ['code' => 'TZS', 'name' => 'Tanzanian Shilling', 'symbol' => 'TSh', 'decimals' => 0],
            ['code' => 'UAH', 'name' => 'Ukrainian Hryvnia', 'symbol' => '₴', 'decimals' => 2],
            ['code' => 'UGX', 'name' => 'Ugandan Shilling', 'symbol' => 'USh', 'decimals' => 0],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'UYU', 'name' => 'Uruguayan Peso', 'symbol' => '$', 'decimals' => 2],
            ['code' => 'UZS', 'name' => 'Uzbekistan Som', 'symbol' => 'UZS', 'decimals' => 0],
            ['code' => 'VEF', 'name' => "Venezuelan Bol\u00edvar", 'symbol' => 'Bs.F.', 'decimals' => 2],
            ['code' => 'VND', 'name' => 'Vietnamese Dong', 'symbol' => '₫', 'decimals' => 0],
            ['code' => 'XAF', 'name' => 'CFA Franc BEAC', 'symbol' => 'FCFA', 'decimals' => 0],
            ['code' => 'XOF', 'name' => 'CFA Franc BCEAO', 'symbol' => 'CFA', 'decimals' => 0],
            ['code' => 'YER', 'name' => 'Yemeni Rial', 'symbol' => 'ر.ي.‏', 'decimals' => 0],
            ['code' => 'ZAR', 'name' => 'South African Rand', 'symbol' => 'R', 'decimals' => 2],
            ['code' => 'ZMK', 'name' => 'Zambian Kwacha', 'symbol' => 'ZK', 'decimals' => 0],
            ['code' => 'ZWL', 'name' => 'Zimbabwean Dollar', 'symbol' => 'ZWL$', 'decimals' => 0],
        ];
    }
}
